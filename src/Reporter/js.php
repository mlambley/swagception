<script type="text/javascript">
$(document).ready(function() {
    $("#linkAll").click(function(e) {
        e.preventDefault();
        if ($(this).html() === "[Collapse All]") {
            $.each($(".link"), function() {
                hide($(this)[0]);
            });
            $(this).html("[Expand All]");
        } else {
            $.each($(".link"), function() {
                show($(this)[0]);
            });
            $(this).html("[Collapse All]");
        }
    });
    $(".link").click(function(e) {
        e.preventDefault();

        if ($(this).html() === "[Collapse]") {
            hide($(this)[0]);
        } else {
            show($(this)[0]);
        }
    });
});
function show(link) {
    var elm = $("[id=\'details" + link.id.replace("link", "") + "\']");
    elm.show();
    $(link).html("[Collapse]");
}
function hide(link) {
    var elm = $("[id=\'details" + link.id.replace("link", "") + "\']");
    elm.hide();
    $(link).html("[Expand]");
}
</script>
