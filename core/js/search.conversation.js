// Search (conversation list) JavaScript.

var ETSearch = {

// The current search details.
currentSearch: "",
currentChannels: [],

// References to search form elements.
form: null,
formInput: null,
formReset: null,

updateInterval: null,

// Initialize the search page.
init: function() {

	// Set the current channel and search query.
	if (ET.currentChannels) ETSearch.currentChannels = ET.currentChannels;
	if (ET.currentSearch) ETSearch.currentSearch = ET.currentSearch;


	// INITIALIZE THE SEARCH FORM.

	// Get the search form elements.
	ETSearch.form = $("#search");
	ETSearch.formInput = $("#search .text");
	ETSearch.formReset = $("#search .control-reset");

	new ETAutoCompletePopup(ETSearch.formInput, "author:");
	new ETAutoCompletePopup(ETSearch.formInput, "contributor:");

	// Make the controls into a popup button.
	if ($("#searchControls").length) {
		$("#search fieldset").append($("#searchControls").popup({alignment: "right"}));
		$("#search").addClass("hasControls");
	}

	// Add an onclick handler to the search button to perform a search.
	ETSearch.form.submit(function(e) {
		ETSearch.search(ETSearch.formInput.val());
		e.preventDefault();
	});

	// Add a key press handler to clear the search input when escape is pressed.
	ETSearch.formInput.keydown(function(e) {
		if (e.which != 27) return;

		// If the value isn't empty, clear it and focus on the input.
		if (ETSearch.formInput.val() != "") {
                        ETSearch.form.submit();
		}
		// If it is already empty, unfocus from the input.
		else ETSearch.formInput.blur();
		e.preventDefault();
	})

	// Add a key press handler to make the 'x' button visible if text has been entered or have previously been entered.
	.keyup(function(e) {
		ETSearch.formReset.css("visibility", (ETSearch.formInput.val() != "" || ETSearch.currentSearch != "") ? "" : "hidden");
	})

	// Add a handler to show the gambits section when the search input is active.
	.focus(function() {
            // フォーカス時 ポップアップ表示しない
	});

	// If the search input is blank, hide the reset 'x' button.
	if (!ETSearch.currentSearch) ETSearch.formReset.css("visibility", "hidden");

	// Add a click handler to the reset 'x' button.
	ETSearch.formReset.click(function(e) {
		ETSearch.search("");
		ETSearch.formInput.focus();
		e.preventDefault();
	});

},


// Get a list of slugs of the currently selected channels.
getCurrentChannelSlugs: function() {
	var slugs = [];
	if (ETSearch.currentChannels.length) {
		for (var i in ETSearch.currentChannels) {
			if (ET.channels[ETSearch.currentChannels[i]]) slugs.push(encodeURIComponent(ET.channels[ETSearch.currentChannels[i]]));
			else slugs.push("");
		}
	}
	else slugs = ["all"];

	return slugs;
},

// Perform a search.
search: function(query, customMethod) {

	// Hide the gambits popup.
//	$("#gambits").fadeOut("fast");

	// Set the current search and the form input value.
	ETSearch.currentSearch = ETSearch.formInput.val(query).val();

	// If the search input is blank, hide the reset 'x' button.
	ETSearch.formReset.css("visibility", ETSearch.currentSearch ? "visible" : "hidden");

	// Get the channel slugs and join them together so we can put them in a URL.
	var channelString = ETSearch.getCurrentChannelSlugs().join("+");

	// Create a history entry so we can use the back button even though we're making an AJAX request.
	$.history.load("conversations/"+channelString+(query ? "?search="+encodeURIComponent(query) : ""), true);

	// Clear the results update timeout.
	ETSearch.updateInterval.reset();

	// Make the request.
	$.ETAjax({
		id: "search",
		url: "conversations/"+(customMethod ? customMethod+".ajax" : "index.ajax")+"/"+channelString,
		type: "post",
		global: false,
		data: {search: query},
		success: function(data) {

			// If messages were returned, don't update the results.
			if (data.messages) return;

			// Display the new results.
			$("#conversations").html(data.view);

			// Update the channels and re-initialize everything.
			ETSearch.updateChannels(data.channels);
			ETSearch.initSearchResults();
			ETMessages.hideMessage("search");

		},
		beforeSend: function() {
			createLoadingOverlay("conversations", "conversations");
		},
		complete: function() {
			hideLoadingOverlay("conversations", false);
		}
	});
},

};

$(function() {
	ETSearch.init();
});
