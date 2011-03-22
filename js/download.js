$(function() {
	UpdateJDStatus();
});

function CheckJD(link) {
	if (!jdownloader) {
		$(link).css("color", "red");
	} else {
		$(link).css("color", "");
	}
}

function UpdateJDStatus() {
	if (jdownloader) {
		$(".cnlWarning").hide();
	} else {
		$(".cnlLink").css("color", "red");
	}
}

function ProcessRegexpNow(id) {
	var url = "index.php?run=settings&ajax=1&do=regexp&id="+encodeURIComponent(id);
	$("#modal_loading").show();
	$.get(url, function() {
		$("#modal_loading").hide();
	});
}

function ProcessCatLinkNow(id) {
	var url = "index.php?run=settings&ajax=1&do=catlinks&id="+encodeURIComponent(id);
	$("#modal_loading").show();
	$.get(url, function() {
		$("#modal_loading").hide();
	});
}

function GetLinks(linkIds, captchaUrl, captchaIdent, captchaText) {
	var url = "index.php?run=download&ajax=1&links="+encodeURIComponent(linkIds);
	if ((captchaUrl != null) && (captchaText != null)) {
		url += "&captchaUrl="+encodeURIComponent(captchaUrl)+"&captchaText="+encodeURIComponent(captchaText)+"&captchaIdent="+encodeURIComponent(captchaIdent);
	}
	$.get(url, function(result) {
		if (typeof result == "string") {
			$("#download_popup").dialog({ title: 'Captcha eingeben' }).html(result);
			$("#captchaButton").button().click(function() {
				SendCaptcha(linkIds);
			});
		} else {
			$("#download_popup").dialog("close");
			AddLinks(document.location.href, result);
		}
	});
}

function SendCaptcha(linkIds) {
	var captchaUrl = $("#captchaUrl").val();
	var captchaIdent = $("#captchaIdent").val();
	var captchaText = $("#captchaInput").val();
	GetLinks(linkIds, captchaUrl, captchaIdent, captchaText);
}

function AddLinks(source, arLinks) {
	$.post("http://127.0.0.1:9666/flash/add", {
		source: source,
		passwords: "",
		urls: arLinks.join("\n")
	}, function(result) {
		alert(result);
	});
}