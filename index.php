<script>
$(document).ready(function() {
    // Buchen-Formular senden
    $('.buchung-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + "&action=buchen";
        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: formData,
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

    // Freigabe-Formular senden
    $('.freigabe-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + "&action=freigeben";
        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: formData,
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });
});
</script>

</body>
</html>