<!-- --><script>
$(function(){
    if ($(".comments:not(:header)").size()) {
        $("#add_comment").append($(document.createElement("input")).attr({ type: "hidden", name: "ajax", value: "true", id: "ajax" }))
        $("#add_comment").ajaxForm({ dataType: "json", resetForm: true, beforeSubmit: function() {
            $("#add_comment").loader();
        }, success: function(json){
            $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", { action: "show_comment", comment_id: json.comment_id, reason: "added" }, function(data) {
                if ($(".comment_count").size() && $(".comment_plural").size()) {
                    var count = parseInt($(".comment_count:first").text())
                    count++
                    $(".comment_count").text(count)
                    var plural = (count == 1) ? "" : "s"
                    $(".comment_plural").text(plural)
                }
                $("#last_comment").val(json.comment_timestamp)
                $(data).prependTo(".comments:not(:header)").hide().fadeIn("slow")
            }, "html")
        }, complete: function(){
            $("#add_comment").loader(true)
        } })
        $("#add_comment").append($(document.createElement("input")).attr({ type: "hidden", name: "parent_id", value: 0, id: "parent_id" }))
    }

})

var editing = 0
var notice = 0
var Comment = {
    delete_animations: { height: "hide", margin: "hide", opacity: "hide" },
    delete_wrap: "",
    reload: function() {
        if ($(".comments:not(:header)").attr("id") == undefined) return;

        var id = $(".comments:not(:header)").attr("id").replace(/comments_/, "")
        if (editing == 0 && notice == 0 && $(".comments:not(:header)").children().size() < 25) {
            $.ajax({ type: "post", dataType: "json", url: "http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", data: "action=reload_comments&post_id="+id+"&last_comment="+$("#last_comment").val(), success: function(json) {
                $("#last_comment").val(json.last_comment)
                $.each(json.comment_ids, function(i, id) {
                    $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", { action: "show_comment", comment_id: id }, function(data){
                        $(data).appendTo(".comments:not(:header)").hide().fadeIn("slow")
                    }, "html")
                })
            } })
        }
    },
    edit: function(id) {
        editing++
        $("#comment_"+id).loader()
        $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", { action: "edit_comment", comment_id: id }, function(data) {
            if (isError(data)) return $("#comment_"+id).loader(true)
            $("#comment_"+id).loader(true).fadeOut("fast", function(){ $(this).empty().append(data).fadeIn("fast", function(){
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
                $("#comment_cancel_edit_"+id).click(function(){
                    $("#comment_"+id).loader()
                    $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", { action: "show_comment", comment_id: id }, function(data){
                        $("#comment_"+id).loader(true).replaceWith(data)
                    })
                })
                $("#comment_edit_"+id).ajaxForm({ beforeSubmit: function(){
                    $("#comment_"+id).loader()
                }, success: function(response){
                    editing--
                    if (isError(response)) return $("#comment_"+id).loader(true)
                    $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", { action: "show_comment", comment_id: id, reason: "edited" }, function(data) {
                        if (isError(data)) return $("#comment_"+id).loader(true)
                        $("#comment_"+id).loader(true)
                        $("#comment_"+id).fadeOut("fast", function(){
                            $(this).replaceWith(data).fadeIn("fast")
                        })
                    }, "html")
                } })
            }) })
        }, "html")
    },
    destroy: function(id) {
        notice--
        $("#comment_"+id).loader()
        $.post("http://jspc29.x-matter.uni-frankfurt.de/trbweb/includes/ajax.php", { action: "delete_comment", id: id }, function(response){
            $("#comment_"+id).loader(true)
            if (isError(response)) return

            if (Comment.delete_wrap != "")
                $("#comment_"+id).wrap(Comment.delete_wrap).parent().animate(Comment.delete_animations, function(){
                    $(this).remove()
                })
            else
                $("#comment_"+id).animate(Comment.delete_animations, function(){
                    $(this).remove()
                })

            if ($(".comment_count").size() && $(".comment_plural").size()) {
                var count = parseInt($(".comment_count:first").text())
                count--
                $(".comment_count").text(count)
                var plural = (count == 1) ? "" : "s"
                $(".comment_plural").text(plural)
            }
        }, "html")
    }
}
<!-- --></script>
