jQuery(document).ready(function ($) {
    // Function to handle the "Engrave" button click event
    $(document).on('click', '#engrave-button', function () {
        // Show the engraving section
        $('.engraving-section').fadeIn();
    });

    // Function to handle the "Close" button click event in the popup
    $(document).on('click', '#engraving-popup-close', function () {
        // Hide the engraving section
        $('.engraving-section').fadeOut();
    });

    // Function to handle the "Add Engraving" button click event in the popup
    $(document).on('click', '#add-engraving-button', function () {
        // Get the engraving text and style from the input fields
        var engravingText = $('#engraving-text').val();
        var engravingStyle = $('#engraving-style').val();
        let formData = $('form.cart')
        let input = $('<input />').attr('type', 'hidden')
            .attr('name', "engraving-text")
            .attr('value', engravingText)
        formData.append(input)
        input = $('<input />').attr('type', 'hidden')
            .attr('name', "engraving-style")
            .attr('value', engravingStyle)
        formData.append(input)



        var engravingStyleText = $('#engraving-style :selected').text();
        console.log(engravingStyleText);

        // Update the frontend product page with the engraving information
        $('.engrave-info').html('Engraving: ' + engravingText + ' - Style: ' + engravingStyle);

        // Add buttons for editing and removing engraving
        $('.engrave-info').append('<button id="edit-engraving-button">Edit</button>');
        $('.engrave-info').append('<button id="remove-engraving-button">Remove</button>');

        // Hide the engraving section
        $('.engraving-section').fadeOut();
    });

    // Function to handle the "Edit" button click event
    $(document).on('click', '#edit-engraving-button', function () {
        // Show the engraving section
        $('.engraving-section').fadeIn();

        // Get the current engraving text and style
        var currentEngraving = $('#engrave-button').text().trim().split(' - Style: ');
        $('#engraving-text').val(currentEngraving[0].replace('Engraving: ', ''));
        $('#engraving-style').val(currentEngraving[1]);
    });

    // Function to handle the "Remove" button click event
    $(document).on('click', '#remove-engraving-button', function () {
        // Reset the "Engrave" button on the frontend product page
        $('#engrave-button').html('Engrave');

        // Remove the edit and remove buttons
        $('#edit-engraving-button').remove();
        $('#remove-engraving-button').remove();
    });
});


// jQuery(document).ready(function($) {
//     // Function to handle the "Engrave" button click event
//     $('#engrave-button').on('click', function() {
//         // Show the engraving popup
//         $('#engraving-popup').fadeIn();
//     });

//     // Function to handle the "Close" button click event in the popup
//     $('#engraving-popup-close').on('click', function() {
//         // Hide the engraving popup
//         $('#engraving-popup').fadeOut();
//     });

//     // Function to handle the "Add Engraving" button click event in the popup
//     $('#add-engraving-button').on('click', function() {
//         // Get the engraving text and style from the input fields
//         var engravingText = $('#engraving-text').val();
//         var engravingStyle = $('#engraving-style').val();

//         // Update the frontend product page with the engraving information
//         $('#engrave-button').html('Engraving: ' + engravingText + ' - Style: ' + engravingStyle);

//         // Add buttons for editing and removing engraving
//         $('#engrave-button').append('<button id="edit-engraving-button">Edit</button>');
//         $('#engrave-button').append('<button id="remove-engraving-button">Remove</button>');

//         // Hide the engraving popup
//         $('#engraving-popup').fadeOut();
//     });

//     // Function to handle the "Edit" button click event
//     $(document).on('click', '#edit-engraving-button', function() {
//         // Show the engraving popup with the current engraving information
//         var currentEngraving = $('#engrave-button').html().split(' - Style: ');
//         $('#engraving-text').val(currentEngraving[0].replace('Engraving: ', ''));
//         $('#engraving-style').val(currentEngraving[1]);
//         $('#engraving-popup').fadeIn();
//     });

//     // Function to handle the "Remove" button click event
//     $(document).on('click', '#remove-engraving-button', function() {
//         // Reset the "Engrave" button on the frontend product page
//         $('#engrave-button').html('Engrave');

//         // Remove the edit and remove buttons
//         $('#edit-engraving-button').remove();
//         $('#remove-engraving-button').remove();
//     });
// });



