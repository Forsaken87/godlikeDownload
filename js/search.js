var updateTimer = null;

function CheckSearch(event) {
	if (event.keyCode == 13) {
		SendSearch();
	}
}

function CreateSearch(input) {
	$(input).autocomplete({
		source: "index.php?run=search_ajax&ajax=1&do=complete",
		minLength: 2,
		select: function( event, ui ) {
			if (event.keyCode != 13) {
				SendSearch();	
			}
		}
	});
}

function SendSearch(searchPage) {
	var searchText = ($("#dl_search").length > 0 ? $("#dl_search").val() : "");
	var catListChecked = $("input.category_check:checked");
	if (catListChecked.length > 0) {
		var searchCat = "";
		for (var i = 0; i < catListChecked.length; i++) {
			searchCat += (i == 0 ? "" : ",") + $(catListChecked[i]).val();
		}
		StartSearch(searchText, searchCat, searchPage);
	} else {
		StartSearch(searchText, null, searchPage);
	}
}

function StartSearch(searchText, searchCat, searchPage) {
	var cat = "&cat="+(searchCat != null ? encodeURIComponent(searchCat) : "");
	var page = "&page="+(searchPage != null ? encodeURIComponent(searchPage) : 1);
	$("#dl_search_result").html('<div class="ui-state-highlight">Suche l&auml;uft ...</div>');
	$("#dl_search_result").load("index.php?run=search_ajax&ajax=1&q="+encodeURIComponent(searchText)+cat+page);
}

function UpdateManual(idDownload, curPage) {
	$.get('index.php?run=download&ajax=1&id='+idDownload+'&force=1', function() {
		SendSearch(curPage);
	});
}

function UpdateProgress() {
	$.get("index.php?run=search_progress&ajax=1", function(result) {
		if (result.done == false) {
			window.setTimeout(function() {
				UpdateProgress();
			}, 100);
		} else {
			SendSearch();
		}
		$("#progress_pages").html(result.count);
		$("#progress_update").progressbar({ value: result.percent });
	});
}