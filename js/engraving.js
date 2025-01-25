jQuery(document).ready(function ($) {

    // Function to handle engraving preview
    function handlePreview() {
        var engravingText = $('#engraving-text').val();
        var engravingStyle = $('#engraving-style').val();
        var $engravingPreview = $('#engraving-preview'); // The container for the preview

        // Update the preview text and style
        $engravingPreview.text(engravingText);
        $engravingPreview.css({
            'position': 'absolute',
            'top': '50%',
            'left': '50%',
            'transform': 'translate(-50%, -50%)',
            // 'color': engravingStyle,
            'font-family': engravingStyle,
            'font-weight': 'bold',
            'background-color': '#0000004f',
            '-webkit-background-clip': 'text',
            '-moz-background-clip': 'text',
            'background-clip': 'text',
            'color': 'transparent',
            'text-shadow': 'rgba(245,245,245,0.5) 1px 3px 1px',
            'user-select': 'none',

        });
    }

    // Attach a click event handler to the "Preview" button
    $('#preview-button').on('click', function () {
        handlePreview();
    });

});
