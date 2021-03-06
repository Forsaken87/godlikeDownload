<?php

$filterClass = "FilterRSLayer";

class FilterRSLayer implements LinkFilter {
	public function FilterRSLayer() {
		// Nothing
	}

	private function base16Decode($arg){
		$ret="";
		for($i=0;$i<strlen($arg);$i+=2){
			$tmp=hexdec(substr($arg,$i,2));
			$ret.=chr($tmp);
		}
		return $ret;
	}

	public function MatchLink($url) {
		if (preg_match('/rs\-layer.com\//i', $url)) {
			return true;
		}
		return false;
	}

	public function ParseLink($url) {
		$dom_result = new DOMDocument();
		if (@$dom_result->loadHTMLFile($url)) {
			$captcha = $dom_result->getElementById("captcha_image");
			if ($captcha == null) return array($url);
			$captcha_src = $captcha->attributes->getNamedItem("src");
			if ($captcha_src == null) return array($url);
			// Captcha image found
			$imageCaptcha = file_get_contents("http://rs-layer.com/".$captcha_src->nodeValue);
			$base64 = 'data:image/png;base64,'.base64_encode($imageCaptcha);

			$htmlCaptcha =	"<div style='margin: auto;' align='center'>\n".
							"	<input type='hidden' id='captchaUrl' value='".$url."' />\n".
							"	<input type='hidden' id='captchaIdent' value='".$captcha_src->nodeValue."' />\n".
							"	<img width='200' align='center' src='".$base64."' title='captcha' /><br /><br />\n".
							"	<input style='width: 200px;' id='captchaInput' placeholder='Captcha eingeben ...' /><br /><br />\n".
							"	<input style='width: 200px;' id='captchaButton' type='button' value='Abschicken' />\n".
							"</div>\n";
			return $htmlCaptcha;
		}
		return array($url);
	}

	public function SubmitCaptcha($urlReferer, $url, $captchaIdent, $captchaText) {
		$context = stream_context_create(array(
			'http' => array(
				'method' 		=> 'POST',
				'referer'		=> $urlReferer,
				'content' 		=> http_build_query(array('image_name' => $captchaIdent, 'captcha_input' => $captchaText)),
				'content-type'	=> 'application/x-www-form-urlencoded'
			),
		));
		$source = file_get_contents($url, false, $context);
		$dom_result = new DOMDocument();
		if (@$dom_result->loadHTML($source)) {
			$linksKey = "";
			$linksCrypted = "";
			$inputList = $dom_result->getElementsByTagName("input");
			for ($i = 0; $i < $inputList->length; $i++) {
				$inputCur = $inputList->item($i);
				$inputName = $inputCur->attributes->getNamedItem("name");
				$inputValue = $inputCur->attributes->getNamedItem("value");
				if (($inputName != null) && ($inputValue != null)) {
					if (($inputName->nodeValue == "jk") && preg_match("/return \'([0-9a-z]+)\'/i", $inputValue->nodeValue, $ar_matches)) {
						$linksKey = $ar_matches[1];
					}
					if ($inputName->nodeValue == "crypted") {
						$linksCrypted = $inputValue->nodeValue;
					}
				}
			}
			if (!empty($linksKey) && !empty($linksCrypted)) {
				$key=$this->base16Decode($linksKey);
				$crypted=base64_decode($linksCrypted);
				$cp = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
				@mcrypt_generic_init($cp,$key,$key);
				$link = str_replace(chr(0), "", @mdecrypt_generic($cp,$crypted));
				mcrypt_generic_deinit($cp);
				mcrypt_module_close($cp);
				return explode("\n",str_replace("\r\n", "\n", $link));
			}
		}
		return array($urlReferer);
	}
}

?>