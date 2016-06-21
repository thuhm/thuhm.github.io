<!-- --><script>
$(function(){
    // Scan AJAX responses for errors.
    $(document).ajaxComplete(function(event, request){
        var response = request ? request.responseText : null
        if (isError(response))
            alert(response.replace(/(HEY_JAVASCRIPT_THIS_IS_AN_ERROR_JUST_SO_YOU_KNOW|<([^>]+)>\n?)/gm, ""))
    })




    $(".toggle_admin").click(function(){
        if (!$("#admin_bar:visible, #controls:visible").size())
            Cookie.destroy("hide_admin")
        else
            Cookie.set("hide_admin", "true", 30)

        $("#admin_bar, #controls").slideToggle()
        return false
    })

})

var Route = {
    action: "index"
}

var site_url = "http://jspc29.x-matter.uni-frankfurt.de/trbweb"

var Post = {
    delete_animations: { height: "hide", opacity: "hide" },
    delete_wrap: "<div></div>",
    id: 0,
    edit: function(id) {
        Post.id = id
        $("#post_"+id).loader()
        $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", { action: "edit_post", id: id }, function(data) {
            $("#post_"+id).loader(true).fadeOut("fast", function(){
                $(this).replaceWith(data)
                $("#post_edit_form_"+id).css("opacity", 0).animate({ opacity: 1 }, function(){
                    $("#more_options_link_"+id).click(function(){
                        if ($("#more_options_"+id).css("display") == "none") {
                            $(this).empty().append("&uarr; Fewer Options")
                            $("#more_options_"+id).slideDown("slow");
                        } else {
                            $(this).empty().append("More Options &darr;")
                            $("#more_options_"+id).slideUp("slow");
                        }
                        return false;
                    })
                    $("#post_edit_form_"+id).ajaxForm({ beforeSubmit: function(){
                        $("#post_edit_form_"+id).loader()
                    }, success: Post.updated })
                    $("#post_cancel_edit_"+id).click(function(){
                        $("#post_edit_form_"+id).loader()
                        $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", {
                            action: "view_post",
                            context: "all",
                            id: id,
                            reason: "cancelled"
                        }, function(data) {
                            $("#post_edit_form_"+id).loader(true).fadeOut("fast", function(){
                                $(this).replaceWith(data)
                                $(this).hide().fadeIn("fast")
                            })
                        }, "html")
                        return false
                    })
                })
            })
        }, "html")
    },
    updated: function(response){
        id = Post.id
        if (isError(response))
            return $("#post_edit_form_"+id).loader(true)

        if (Route.action != "drafts" && Route.action != "view" && $("#post_edit_form_"+id+" select#status").val() == "draft") {
            $("#post_edit_form_"+id).loader(true).fadeOut("fast", function(){
                alert("Post has been saved as a draft.")
            })
        } else if (Route.action == "drafts" && $("#post_edit_form_"+id+" select#status").val() != "draft") {
            $("#post_edit_form_"+id).loader(true).fadeOut("fast", function(){
                alert("Post has been published.")
            })
        } else {
            $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", {
                action: "view_post",
                context: "all",
                id: id,
                reason: "edited"
            }, function(data) {
                $("#post_edit_form_"+id).loader(true).fadeOut("fast", function(){
                    $(this).replaceWith(data)
                    $("#post_"+id).hide().fadeIn("fast")
                })
            }, "html")
        }
    },
    destroy: function(id) {
        $("#post_"+id).loader()
        $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", { action: "delete_post", id: id }, function(response) {
            $("#post_"+id).loader(true)
            if (isError(response)) return

            if (Post.delete_wrap != "")
                $("#post_"+id).wrap(Post.delete_wrap).parent().animate(Post.delete_animations, function(){
                    $(this).remove()

                    if (Route.action == "view")
                        window.location = "http://jspc29.x-matter.uni-frankfurt.de/trbweb"
                })
            else
                $("#post_"+id).animate(Post.delete_animations, function(){
                    $(this).remove()

                    if (Route.action == "view")
                        window.location = "http://jspc29.x-matter.uni-frankfurt.de/trbweb"
                })
        }, "html")
    },
    prepare_links: function(id) {
        $(".post_edit_link:not(.no_ajax)").live("click", function(){
            var id = $(this).attr("id").replace(/post_edit_/, "")
            Post.edit(id)
            return false
        })

        $(".post_delete_link").live("click", function(){
            if (!confirm("Are you sure you want to delete this post?\n\nIt cannot be restored if you do this. If you wish to hide it, save it as a draft.")) return false
            var id = $(this).attr("id").replace(/post_delete_/, "")
            Post.destroy(id)
            return false
        })
    }
}


//<script>
            $(function(){
                function scanTags(){
                    $(".tags_select a").each(function(){
                        regexp = new RegExp("(, ?|^)"+ $(this).text() +"(, ?|$)", "g")
                        if ($("#tags").val().match(regexp))
                            $(this).addClass("tag_added")
                        else
                            $(this).removeClass("tag_added")
                    })
                }

                scanTags()

                $("#tags").live("keyup", scanTags)

                $(".tag_cloud > span").live("mouseover", function(){
                    $(this).find(".controls").css("opacity", 1)
                }).live("mouseout", function(){
                    $(this).find(".controls").css("opacity", 0)
                })

                $(".tag_cloud span a").draggable({
                    zIndex: 100,
                    revert: true
                });

                $(".post_tags li:not(.toggler)").droppable({
                    accept: ".tag_cloud span a",
                    tolerance: "pointer",
                    activeClass: "active",
                    hoverClass: "hover",
                    drop: function(ev, ui) {
                        var post_id = $(this).attr("id").replace(/post-/, "");
                        var self = this;

                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: "http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php",
                            data: {
                                action: "tag_post",
                                post: post_id,
                                name: $(ui.draggable).text()
                            },
                            beforeSend: function(){
                                $(self).loader()
                            },
                            success: function(json){
                                $(self).loader(true)
                                $(document.createElement("a")).attr("href", json.url).addClass("tag dropped").text(json.tag).insertBefore($(self).find(".edit_tag"))
                            }
                        });
                    }
                });
            })

            function add_tag(name) {
                if ($("#tags").val().match("(, |^)"+ name +"(, |$)")) {
                    regexp = new RegExp("(, |^)"+ name +"(, |$)", "g")
                    $("#tags").val($("#tags").val().replace(regexp, function(match, before, after){
                        if (before == ", " && after == ", ")
                            return ", "
                        else
                            return ""
                    }))

                    $(".tags_select a").each(function(){
                        if ($(this).text() == name)
                            $(this).removeClass("tag_added")
                    })
                } else {
                    if ($("#tags").val() == "")
                        $("#tags").val(name)
                    else
                        $("#tags").val($("#tags").val().replace(/(, ?)?$/, ", "+ name))

                    $(".tags_select a").each(function(){
                        if ($(this).text() == name)
                            $(this).addClass("tag_added")
                    })
                }
            }
<!-- --></script>
