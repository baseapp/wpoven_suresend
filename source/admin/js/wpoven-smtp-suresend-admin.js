(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  function validateEmail(email) {
    var regex = /\S+@\S+\.\S+/;
    return regex.test(email);
  }

  function validateDomain(domain) {
    var domainRegex =
      /^(?!:\/\/)(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}|localhost)$/;
    return domainRegex.test(domain);
  }

  document.addEventListener("DOMContentLoaded", function () {
    var liElement = document.querySelector("li.smtp-logs");
    if (liElement) {
      var firstAElement = liElement.querySelector("a");
      if (firstAElement) {
        firstAElement.remove();
      }
    }

    var element = document.querySelector(
      "li.toplevel_page_wpoven-smtp-suresend"
    );
    if (element) {
      element.remove();
    }

    async function suresendPurgeSMTPLogs() {
      const ajax_nonce = document.getElementById("wpoven-ajax-nonce").innerText;
      const ajax_url = document.getElementById("wpoven-ajax-url").innerText;
      const response = await fetch(ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=utf-8",
        },
        body: `action=wpoven_run_smtp_purge_all_logs&security=${ajax_nonce}`,
        credentials: "same-origin",
        timeout: 10000,
      });

      if (response.ok) {
        const data = await response.json();
        if (data.status === "ok") {
          alert("All SMTP logs have been purged successfully.");
        } else {
          alert("Error purging SMTP logs: " + data.message);
        }
      }
    }

    var PurgeSMTPLogs = document.querySelector('[data-id="purge-smtp-logs"]');

    if (PurgeSMTPLogs) {
      PurgeSMTPLogs.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent any default action
        event.stopPropagation(); // Stop the event from bubbling up
        if (
          confirm(
            "Are you sure you want to purge all SMTP logs? This action cannot be undone."
          )
        ) {
          suresendPurgeSMTPLogs();
        }
      });
    }

    // document
    //   .querySelector("input#redux_bottom_save")
    //   .addEventListener("click", function () {
    //     var domain = document.querySelector("input#dkim-domain");
    //     if (!validateDomain(domain)) {
    //       alert("Please enter a valid domain.");
    //     }
    //   });

    function checkVisibilityAndRefresh() {
      let element = document.querySelector(
        ".saved_notice.admin-notice.notice-green"
      );
      if (element && window.getComputedStyle(element).display !== "none") {
        window.location.reload();
      }
    }
    // Check every 500 milliseconds (0.5 seconds)
    setInterval(checkVisibilityAndRefresh, 500);

    //remove extra menu title
    const menuItems = document.querySelectorAll("li#toplevel_page_wpoven");
    const menuArray = Array.from(menuItems);
    for (let i = 1; i < menuArray.length; i++) {
      menuArray[i].remove();
    }

    document
      .querySelector("input.dkim-copy")
      .addEventListener("click", function () {
        var textarea = document.getElementById("dns-content-textarea");
        navigator.clipboard.writeText(textarea.value).then(
          function () {
            textarea.select();
          },
          function (err) {
            console.error("Could not copy text: ", err);
          }
        );
      });
  });

  $(document).ready(function () {
    $("a.smtp-logs").on("click", function (e) {
      e.preventDefault();
      window.location.href = $(this).attr("href");
    });
  });

  $(function () {
    $('input[data-id="send-smtp-mail-test"]')
      .parent()
      .click(function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var email = document.getElementById("email_to");
        var format = document.getElementById("text_format");
        if (!validateEmail(email.value)) {
          alert("Please enter a valid email address.");
        } else {
          var form = document.createElement("form");
          form.setAttribute("method", "post");

          var emailTo = document.createElement("input");
          emailTo.setAttribute("type", "text");
          emailTo.setAttribute("name", "email_to");
          emailTo.setAttribute("value", email.value);

          var textFormat = document.createElement("input");
          textFormat.setAttribute("type", "text");
          textFormat.setAttribute("name", "text_format");
          textFormat.setAttribute("value", format.value);

          //console.log(format.value);
          form.appendChild(emailTo);
          form.appendChild(textFormat);
          document.body.appendChild(form);

          form.submit();
        }
      });
  });

  $(document).ready(function () {
    $(".open-modal-btn").on("click", function () {
      var modalId = $(this).data("modal-id");
      var modal = $("#" + modalId);
      modal.css("display", "block");

      var closeButton = modal.find(".close");
      if (closeButton.length) {
        closeButton.on("click", function () {
          modal.css("display", "none");
        });
      }
    });
  });
})(jQuery);
