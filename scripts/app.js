(function ($) {
    "use strict";

    // ---- Favorites (AJAX) ----
    function updateFavoriteButton($button, isLiked) {
        $button.attr("data-liked", isLiked ? "1" : "0");
        $button.attr("aria-pressed", isLiked ? "true" : "false");
        $button.toggleClass("is-liked", isLiked);
        $button.text(isLiked ? "Remove from favorites" : "Add to favorites");
    }

    function removeFavoriteItem($button) {
        var $item = $button.closest("[data-favorite-item='1']");
        if (!$item.length) {
            return;
        }
        $item.fadeOut(400, function () {
            $(this).remove();
            if ($("[data-favorite-item='1']").length === 0) {
                $("#favorites-grid").hide();
                $("#favorites-empty-state").removeClass("is-hidden").show();
            }
        });
    }

    $(document).on("click", ".fav-toggle", function () {
        var $button = $(this);
        var recipeId = parseInt($button.attr("data-recipe-id"), 10);

        if (!recipeId || $button.prop("disabled")) {
            return;
        }

        $button.prop("disabled", true);

        $.post("toggle_fav.php", { recipe_id: recipeId })
            .done(function (response) {
                var res = String(response).trim();
                if (res === "added") {
                    updateFavoriteButton($button, true);
                    return;
                }
                if (res === "removed") {
                    updateFavoriteButton($button, false);
                    removeFavoriteItem($button);
                    return;
                }
                window.alert("Could not update favorites.");
            })
            .fail(function () {
                window.alert("Could not update favorites.");
            })
            .always(function () {
                $button.prop("disabled", false);
            });
    });

    // ---- Live search (AJAX, debounced) ----
    var searchTimer = null;
    var activeSearchRequest = null;
    var $searchInput = $("#search-query");

    if ($searchInput.length) {
        $searchInput.on("keyup", function () {
            var query = String($(this).val() || "").trim();
            window.clearTimeout(searchTimer);

            if (query.length < 2) {
                if (activeSearchRequest) {
                    activeSearchRequest.abort();
                    activeSearchRequest = null;
                }
                $("#default-recipe-list").show();
                $(".filter-bar, .filter-select").show();
                $("#search-results").empty().hide();
                return;
            }

            searchTimer = window.setTimeout(function () {
                if (activeSearchRequest) {
                    activeSearchRequest.abort();
                }
                $("#default-recipe-list").hide();
                $(".filter-bar, .filter-select").hide();
                $("#search-results").html("<p>Searching...</p>").show();

                activeSearchRequest = $.get("search_ajax.php", { search_query: query })
                    .done(function (html) {
                        $("#search-results").html(html).show();
                    })
                    .fail(function (jqXHR, textStatus) {
                        if (textStatus !== "abort") {
                            $("#search-results")
                                .html('<p class="message error">Search failed. Please try again.</p>')
                                .show();
                        }
                    })
                    .always(function () {
                        activeSearchRequest = null;
                    });
            }, 300);
        });
    }

    // ---- Category dropdown filter (navigates to filtered page) ----
    var $categorySelect = $("#category-select");
    if ($categorySelect.length) {
        $categorySelect.on("change", function () {
            var id = parseInt($(this).val(), 10);
            if (!id) {
                window.location.href = "index.php";
            } else {
                window.location.href = "index.php?category=" + id;
            }
        });
    }

    // ---- Delete own comment (AJAX) ----
    $(document).on("click", ".delete-comment", function () {
        var $button = $(this);
        var commentId = parseInt($button.attr("data-comment-id"), 10);
        var $item = $button.closest("[data-comment-item='1']");

        if (!commentId || $button.prop("disabled")) {
            return;
        }
        if (!window.confirm("Delete this comment?")) {
            return;
        }

        $button.prop("disabled", true);

        $.post("delete_comment_ajax.php", { comment_id: commentId })
            .done(function (response) {
                if (String(response).trim() !== "ok") {
                    window.alert("Could not delete the comment.");
                    return;
                }
                $item.fadeOut(400, function () {
                    $(this).remove();
                    if ($("[data-comment-item='1']").length === 0) {
                        $("#comments-empty-state").removeClass("is-hidden").show();
                    }
                });
            })
            .fail(function () {
                window.alert("Could not delete the comment.");
            })
            .always(function () {
                $button.prop("disabled", false);
            });
    });
}(jQuery));
