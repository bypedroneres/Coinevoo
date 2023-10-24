$(document).ready(function() {
    // When the profile picture is clicked, trigger the file input click
    $("#profile-picture").click(function() {
        $("#profile-picture-upload").click();
    });

    // When a new image is selected, update the background image of the .profile-picture div
    $("#profile-picture-upload").change(function() {
        var selectedFile = this.files[0];

        if (selectedFile) {
            var imageUrl = URL.createObjectURL(selectedFile);

            // Update the background image of the .profile-picture div
            $(".profile-picture").css("background-image", "url(" + imageUrl + ")");
        }
    });
});
