var captcha_loaded = false;

function recaptchaCallback() {
	$.get('/~rl').done(function(data) {
		if (!data.status) {
			recaptchaLoad();
			captcha_loaded = true;
		}
	});
}

function recaptchaLoad() {
	grecaptcha.render("recaptcha-target", {
		"sitekey": $("#recaptcha-target").attr("data-sitekey")
	});
}

(function() {
var list = $("#shortened-urls-list");
var clipboard = new Clipboard(".copy-button");
var shorten_button = $("#shorten-button");

clipboard.on("success", function(e) {
	var jqelement = $(e.trigger);
	jqelement.attr("title", "Copied!").tooltip("show");
	setTimeout(function() {
		jqelement.attr("title", "").tooltip("destroy");
	}, 800);
});

function showAlert(text) {
	$("#home-alert-message").removeClass("hidden").html(text);
}

function hideAlert() {
	$("#home-alert-message").addClass("hidden");
}

function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

function loadHistory() {
	list.append($("<div/>", {
		"class": "centered-text lowered-element history-refresh-spinner",
	}).html('<i class="fa fa-spin fa-fw fa-3x fa-refresh"></i><br>Fetching history...'));

	var time = list.attr("data-history-time");
	var num = list.attr("data-history-num");

	$.get("/~history", {
		t: time,
		s: num
	}).done(function(data) {
		var len = data.result.length;

		for (var i = len - 1; i >= 0; i--) {
			addCard(data.result[i]);
		}

		if (len < 15) {
			list.attr("data-loading-disabled", true);
		}

		var newnum = Number(num) + len;

		list.attr("data-history-num", newnum);

		if (newnum === 0) {
			list.append($("<div/>", {
				"class": "row centered-text lowered-element"
			}).append($("<img/>", {
				"class": "monkey-face",
				"src": "/assets/img/monkey_face.svg"
			})).append($("<h4/>").text("You don't seem to have shortened any links. Go ahead and shorten one now!")));

			list.attr("data-not-shortened", true);
		}
	}).fail(function() {
		list.append($("<div/>", {
			"class": "alert alert-danger",
		}).text("Failed to retrieve history. Please refresh this page."));

		list.attr("data-loading-disabled", true);
	}).always(function() {
		$(".history-refresh-spinner").remove();
	});
}

function addCard(data) {
	var shorturl = window.location.protocol + "//" + window.location.host + "/" + data.shorturl;
	var enc_url = encodeURIComponent(shorturl);

	var panel = $("<div/>", {
		"class": "panel panel-default link-card"
	});
	var panelbody = $("<div/>", {
		"class": "panel-body row"
	});
	var panelfooter = $("<div/>", {
		"class": "panel-footer"
	});

	var link_details = $("<div/>", {
		"class": "link-details col-sm-9"
	});

	var link_title = $("<span/>", {
		"class": "link-title"
	}).text(data.title);

	var original_link = $("<span/>", {
		"class": "original-link"
	}).text(data.url);

	var short_link = $("<a/>", {
		"class": "link-href",
		"href": shorturl
	}).text(shorturl);

	link_details.append(link_title).append(original_link).append(short_link);

	var analytics_details = $("<div/>", {
		"class": "analytics-details col-sm-3"
	});

	var click_count = $("<span/>", {
		"class": "click-count"
	}).text(data.clicks || 0);

	var click_word = $("<span/>", {
		"class": "click-word"
	}).text("clicks");

	var analytics_button = $("<a/>", {
		"class": "btn btn-primary hidden-xs",
		"href": "/~analytics/" + data.shorturl
	}).text("View analytics");

	var mobile_copy_button = $("<a/>", {
		"class": "copy-button btn btn-default visible-xs-inline",
		"data-clipboard-text": shorturl
	}).html('<i class="fa fa-copy"></i> Copy link');

	analytics_details.append(click_count).append(click_word).append(analytics_button).append(mobile_copy_button);
	panelbody.append(link_details).append(analytics_details);

	var bottom_row = $("<div/>", {
		"class": "row hidden-xs"
	});

	var left_col = $("<div/>", {
		"class": "col-sm-6"
	});

	var copy_button = $("<a/>", {
		"class": "copy-button btn btn-default",
		"data-clipboard-text": shorturl
	}).html('<i class="fa fa-copy"></i> Copy link');

	var qrcode_button = $("<a/>", {
		"class": "btn btn-default",
		"href": "/~qr/" + data.shorturl
	}).html('<i class="fa fa-qrcode"></i> Get QR code');

	left_col.append(copy_button).append(qrcode_button);

	var right_col = $("<div/>", {
		"class": "col-xs-7 col-sm-6 share-icons"
	});

	var fb_share_icon = $("<a/>", {
		"class": "fa fa-2_5x fa-facebook-square",
		"href": "https://www.facebook.com/sharer/sharer.php?u=" + shorturl,
		"title": "Share on Facebook",
		"target": "_blank"
	});

	var twitter_share_icon = $("<a/>", {
		"class": "fa fa-2_5x fa-twitter-square",
		"href": "https://twitter.com/share?url=" + shorturl,
		"title": "Share on Twitter",
		"target": "_blank"
	});

	var gplus_share_icon = $("<a/>", {
		"class": "fa fa-2_5x fa-google-plus-square",
		"href": "https://plus.google.com/share?url=" + shorturl,
		"title": "Share on Google Plus",
		"target": "_blank"
	});

	var mobile_row = $("<div/>", {
		"class": "row visible-xs"
	});

	var mobile_container = $("<div/>", {
		"class": "col-xs-12 mobile-action-icons"
	});

	var mobile_qrcode_button = $("<a/>", {
		"class": "fa fa-2_5x fa-qrcode",
		"href": "/~qr/" + data.shorturl
	});

	var mobile_analytics_button = $("<a/>", {
		"class": "fa fa-2_5x fa-bar-chart",
		"href": "/~analytics/" + data.shorturl
	});

	right_col.append(fb_share_icon)
	         .append(twitter_share_icon)
	         .append(gplus_share_icon);

	bottom_row.append(left_col)
	          .append(right_col);

	mobile_container.append(mobile_qrcode_button)
	                .append(mobile_analytics_button)
	                .append(fb_share_icon.clone())
	                .append(twitter_share_icon.clone())
	                .append(gplus_share_icon.clone());

	if (/Android|iPhone/.test(navigator.userAgent)) {
		var whatsapp_share_icon = $("<a/>", {
			"class": "fa fa-2_5x fa-whatsapp",
			"href": "whatsapp://send?text=" + enc_url,
			"data-action": "share/whatsapp/share",
			"title": "Share via Whatsapp"
		});

		right_col.append(whatsapp_share_icon);
		mobile_container.append(whatsapp_share_icon.clone());
	}

	mobile_row.append(mobile_container);

	panelfooter.append(bottom_row)
	           .append(mobile_row);

	panel.append(panelbody)
	     .append(panelfooter);

	list.prepend(panel);

	$("#shortened-urls-container").removeClass("hidden");
}

function shortenLink() {
	hideAlert();

	var data = {};
	var url = $("#input-url").val().trim();

	if (url === "") {
		showAlert("Please enter an URL to shorten.");
		return;
	}

	data.url = url;

	if ($("#custom-url-options").hasClass("in")) {
		var shorturl = $("#custom-url").val().trim();

		if (shorturl !== "") {
			if (!/^[a-z0-9-]{1,70}$/i.test(shorturl)) {
				showAlert("That is not a valid short URL. A short URL may contain only alphabets, numbers and hyphens (-).");
				return;
			}

			data.shorturl = shorturl;
		}
	}

	shorten_button.prop('disabled', true);
	var shorten_button_html = shorten_button.html();
	shorten_button.html('<i class="fa fa-refresh fa-spin fa-fw"></i>');

	$.post('/~shorten', data).done(function(data) {
		if (data.status) {
			if (list.attr("data-not-shortened")) {
				list.empty();
				list.removeAttr("data-not-shortened");
			}

			$('#promo-image').remove();
			addCard(data);

			if (!list.attr("data-history-time")) {
				var offset = $("#main-promo-text").offset().top;

				if ($(window).scrollTop() < offset) {
					$('html, body').animate({
						scrollTop: offset
					}, 1000);
				}

				var shorturl_data = sessionStorage.getItem("data");

				if (shorturl_data) {
					shorturl_data = JSON.parse(shorturl_data);
				}
				else {
					shorturl_data = [];
				}

				shorturl_data.push(data);

				window.sessionStorage.setItem("data", JSON.stringify(shorturl_data));
			}
		}
		else if (data.reason === "shorturl_taken") {
			showAlert("Sorry, that short URL is already taken.");
		}
		else if (data.reason === "invalid_url") {
			showAlert("Sorry, the URL you entered is not valid.");
		}
		else if (data.reason === "ratelimited") {
			showAlert("You did not fill in the captcha.");
		}
		else {
			showAlert("Oops! Shortening that URL failed due to an unexpected error. If the problem persists, please <a href=\"https://www.booleanworld.com/contact-us\">contact us</a>.");
		}
	}).fail(function() {
		showAlert("Oops! Shortening that URL failed due to an unexpected error. If the problem persists, please <a href=\"https://www.booleanworld.com/contact-us\">contact us</a>.");
	}).always(function() {
		shorten_button.html(shorten_button_html);
		shorten_button.prop('disabled', false);

		$.get('/~rl').done(function(data) {
			if (data.status) {
				$("#recaptcha-target").html("");
				captcha_loaded = false;
			}
			else if (!captcha_loaded) {
				recaptchaLoad();
				captcha_loaded = true;
			}
			else {
				grecaptcha.reset();
			}
		});
	});
}

shorten_button.click(function() {
	shortenLink();
});

$("#input-url, #custom-url-options").keypress(function(ev) {
	if (ev.keyCode === 13) {
		shortenLink();
	}
});

if (list.attr("data-history-time")) {
	window.onscroll = debounce(function() {
		if (list.attr("data-loading-disabled")) {
			return;
		}

		var scrollTop = (document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop;
		var scrollHeight = (document.documentElement && document.documentElement.scrollHeight) || document.body.scrollHeight;

		if ((scrollTop + window.innerHeight) >= scrollHeight) {
			loadHistory();
		};
	}, 500);

	loadHistory();
}
else {
	var shorturl_data = sessionStorage.getItem("data");

	if (shorturl_data) {
		shorturl_data = JSON.parse(shorturl_data);
		var len = shorturl_data.length;

		$('#promo-image').remove();

		for (var i = 0; i < len; i++) {
			addCard(shorturl_data[i]);
		}
	}
}

})();
