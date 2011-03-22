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
	// Calculate number of rows fitting on screen
	var row_count = Math.floor(($("#dl_search_result").parent().height() - 128) / 24);
	// Calculate an secure number of characters available for the title
	var title_len = Math.floor(($("#dl_search_result").parent().width() - 580) / 9);
	var cat = "&cat="+(searchCat != null ? encodeURIComponent(searchCat) : "");
	var page = "&page="+(searchPage != null ? encodeURIComponent(searchPage) : 1);
	var url = "index.php?run=search_ajax&ajax=1&rows="+row_count+"&length="+title_len+"&q="+encodeURIComponent(searchText)+cat+page;
	$("#dl_search_result").html('<div class="ui-state-highlight">Suche l&auml;uft ...</div>');
	$("#dl_search_result").load(url);
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