let segments;

jQuery(document).ready(function ($) {
    /**
     * -----------------------------
     * INITIALIZATION & VARIABLES
     * -----------------------------
     */

    // Ensure Winwheel.js library is loaded, otherwise abort.
    if (typeof Winwheel === 'undefined') {
        console.error('Winwheel.js not loaded.');
        return;
    }

    // DOM references
    const wheelContainer = $('#swn-wheel-container');
    const messageArea = $('#swn-message-area');
    const spinChancesDisplay = $('.swn-spin-chances');
    const spinTriggerButton = $('#swn-spin-trigger');

    // Wheel instance + control flags
    let theWheel = null;
    let wheelSpinning = false;

    // Track how many spins user still has (from server via wp_localize_script)
    let currentSpinChances = parseInt(swn_params.user_spin_chances) || 0;

    // Success sound (played when user wins)
    let success_audio = new Audio(swn_params.success_audio_url);
    success_audio.preload = 'auto';
    success_audio.load();

    // Wheel segments (prizes). Convert escaped "\n" into real line breaks for labels.
    segments = swn_params.segments.map(segment => ({
        ...segment,
        text: segment.text.replace(/\\\\n/g, '\n')
    }));

    // console.log(segments);

    // If no segments configured in admin, abort early with a message.
    if (swn_params.segments && swn_params.segments.length > 0) {
        initWheel();
        winwheelResize()  // (winWheel.js library helper function, for resizing the wheel)
    } else {
        if (wheelContainer.length) {
            // alert(wheelContainer.length);
            messageArea.html('<p>' + 'No prizes configured for the wheel.' + '</p>');
        }
        console.warn('Spin & Win: No segments found to create the wheel.');
        return;
    }


    /**
     * -----------------------------
     * WHEEL SETUP
     * -----------------------------
     */
    function initWheel() {
        if (!$('#swn-canvas').length) return; // Canvas must exist

        // Create a new Winwheel instance with settings passed from PHP
        theWheel = new Winwheel({
            'canvasId': 'swn-canvas',
            'numSegments': swn_params.numSegments,
            'responsive': true,
            'outerRadius': swn_params.outer_radius || 400,
            'innerRadius': swn_params.inner_radius || 0, // Make it a pie or donut (0 = pie, >0 = donut)
            'lineWidth': swn_params.wheel_line_width || 2,
            'strokeStyle': swn_params.wheel_stroke_color || '#FFFFFF',
            'textFontSize': swn_params.text_size || 16,
            'textFillStyle': swn_params.wheel_text_color || '#ffffff',
            'textAlignment': 'center',
            // 'textMargin'  : 55,
            'textFontFamily': 'IRANSansXFaNum',
            'segments': segments, // Loaded from wp_localize_script
            'animation': {
                'type': 'spinToStop',
                'duration': 10, // Duration in seconds
                'spins': 10, // Number of spins
                'callbackFinished': alertPrize, // Called when spin stops
                'callbackAfter': drawTriangle, // Draw pointer after each animation frame
                'callbackSound': playSound, // Tick sound while spinning
            },
            'pins': { // (Optional) Decorative pins around wheel
                'number': swn_params.numSegments * 2, // 2 pins per segment
                'fillStyle': 'white',
                'strokeStyle': '#FFFFFF',
                'outerRadius': 3,
                'responsive': true // This must be true if responsive is true
            }
        });

        drawTriangle(); // Initial draw of pointer

        // let resizeTimeout;
        // window.addEventListener('load', drawTriangle);
        // window.addEventListener('resize', () => {
        //     debugger;
        //     clearTimeout(resizeTimeout);
        //     resizeTimeout = setTimeout(drawTriangle, 200); // adjust delay as needed
        // });


        // Tick audio (played each time wheel passes a pin)
        // Create audio object and load audio file.
        let audio = new Audio(swn_params.tick_audio_url);
        audio.preload = 'auto';
        audio.load();
        function playSound() {
            // Stop and rewind the sound if it already happens to be playing.
            audio.pause();
            audio.currentTime = 0;

            // Play the sound.
            audio.play();
        }

        // Optional: draw external pointer image instead of triangle
        if (swn_params.pin_image_url && $('#swn-canvas').length) {
            let pinImage = new Image();
            pinImage.onload = function () {

                // Position the pin image centered on top of the canvas
                // This is a basic example; you might need to adjust dynamically
                let canvasElement = document.getElementById('swn-canvas');
                let pinElement = document.createElement('img');
                pinElement.src = swn_params.pin_image_url;
                pinElement.style.position = 'absolute';
                pinElement.style.left = (canvasElement.offsetLeft + canvasElement.offsetWidth / 2 - pinImage.width / 2) + 'px';
                pinElement.style.top = (canvasElement.offsetTop - pinImage.height / 2 + 15) + 'px'; // Adjust +15 as needed
                pinElement.style.zIndex = '100'; // Ensure it's above the canvas
                // canvasElement.parentNode.insertBefore(pinElement, canvasElement.nextSibling);
                // Simpler if using a wrapper and absolute positioning the pin on top/center of canvas via CSS.
                // For Winwheel's own pin drawing feature (if it has one for a static pointer, not the ones on the wheel itself):
                // theWheel.drawPin(); // Check Winwheel.js docs for static pointer options.
                // The common approach is a separate image element positioned over the wheel.
            }
            pinImage.src = swn_params.pin_image_url;
        }
    }

    /**
     * Draw static triangle pointer on top of the wheel
     */
    function drawTriangle() {
        if (!theWheel) return;

        const canvas = document.getElementById('swn-canvas');
        // Get the 2D rendering context for the canvas
        const ctx = canvas.getContext('2d');

        // const imageUrl = 'https://placehold.co/300x150/60a5fa/ffffff?text=Your+Logo';

        // const img = new Image();
        // img.src = imageUrl;

        // --- Important: Wait for the image to load before drawing it ---
        /*
        img.onload = () => {
            // Calculate the x-coordinate to center the image horizontally
            // (canvas width - image width) / 2
            const imageX = (canvas.width - img.width) / 2;

            // Define the y-coordinate for the top of the canvas
            // You can add a small padding if you want it slightly below the very top
            const imageY = 0; // Or 10 for a 10px margin from the top

            // Clear the canvas before drawing, especially if you plan to redraw
            // In this specific case, it's not strictly necessary if canvas is initially empty,
            // but it's good practice for dynamic updates.
            // ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw the image onto the canvas
            // drawImage(image, dx, dy, dWidth, dHeight)
            // dx, dy: The x and y coordinates where to place the image on the canvas.
            // dWidth, dHeight: The width and height to draw the image.
            ctx.drawImage(img, imageX, imageY, img.width, img.height);

            // Optional: Add some text on the canvas to indicate the image
            ctx.font = '20px Inter';
            ctx.fillStyle = '#1e3a8a'; // Dark blue color
            ctx.textAlign = 'center';
            ctx.fillText('Image Displayed Above', canvas.width / 2, imageY + img.height + 30);
        };
        */


        let ctx_a = theWheel.ctx;
        if (ctx_a) {
            ctx_a.fillStyle = swn_params.wheel_pointer_color || '#000000'; // Pointer color
            ctx_a.beginPath();
            // Adjust coordinates to point from top-center towards the center
            let centerX = theWheel.centerX;
            let outerRadius = theWheel.outerRadius;
            ctx_a.moveTo(centerX - 15, outerRadius * 0.01); // Top-left of pointer base
            ctx_a.lineTo(centerX + 15, outerRadius * 0.01); // Top-right of pointer base
            ctx_a.lineTo(centerX, outerRadius * 0.10);    // Point towards center
            ctx_a.lineTo(centerX - 15, outerRadius * 0.01); // Back to start
            ctx_a.fill();
        }
    }


    /**
     * -----------------------------
     * SPIN BUTTON HANDLER
     * -----------------------------
     */
    $('#swn-spin-trigger').on('click', function () {
        // Prevent multiple spins at the same time
        if (wheelSpinning) return;

        // Require login
        if (!swn_params.user_logged_in) {
            messageArea.text(swn_params.not_logged_in_message).addClass('swn-error');
            return;
        }

        // Check spin chances
        if (currentSpinChances <= 0) {
            messageArea.text(swn_params.no_spins_message).addClass('swn-error');
            // Disable spin button
            $(this).prop('disabled', true).css('opacity', 0.5);
            return;
        }

        // Mark spinning state and update UI
        // wheelSpinning = true;
        // $(this).prop('disabled', true).css('opacity', 0.7);
        // messageArea.text(swn_params.spinning_message).removeClass('swn-error swn-success');

        // Perform AJAX request to backend (which decides the winning segment)
        $.ajax({
            url: swn_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'swn_spin_wheel',
                security: swn_params.nonce,
                wheel_id: swn_params.wheel_id
            },
            success: function (response) {
                console.log(response)

                if (response.success) {
                    console.log("swn ajax success");
                    let winningSegmentIndex = segments.findIndex(item => item.id === response.data.prize.id);
                    // winWheel uses indexes for wheel segments starting from 1, not 0, so we add 1 to the found index.
                    winningSegmentIndex++;
                    console.log(segments);
                    if (theWheel && typeof theWheel.startAnimation === 'function' && winningSegmentIndex >= 0) {
                        // Calculate exact angle for chosen segment
                        let stopAtAngle = theWheel.getRandomForSegment(winningSegmentIndex);
                        console.log(winningSegmentIndex);
                        console.log(stopAtAngle);

                        theWheel.animation.stopAngle = stopAtAngle;

                        // Store prize data to show after animation
                        theWheel.userData = {
                            prize_name: response.data.prize.display_name,
                            prize_details: response.data.prize_details,
                            message: response.data.message
                        };
                        theWheel.startAnimation();
                    } else {
                        // Fallback if animation can't start (e.g., bad segment number). directly show prize without animation.
                        alertPrizeDirectly(response.data);
                        wheelSpinning = false;
                        $('#swn-spin-trigger').prop('disabled', false).css('opacity', 1);
                    }
                    currentSpinChances = parseInt(response.data.remaining_spins);
                    updateSpinChancesDisplay();
                } else { // Server returned error (e.g. no spins, invalid nonce, etc.)
                    // console.log("swn ajax error");
                    // console.log(response);
                    messageArea.text(response.data.message || 'An error occurred.').addClass('swn-error');
                    wheelSpinning = false;
                    $('#swn-spin-trigger').prop('disabled', false).css('opacity', 1);
                    if (response.data && typeof response.data.remaining_spins !== 'undefined') {
                        currentSpinChances = parseInt(response.data.remaining_spins);
                        updateSpinChancesDisplay();
                    }
                }
            },
            error: function (response) {
                console.log(response)
                return;

                // Network/connection failure
                messageArea.text('Network error. Please try again.').addClass('swn-error');
                wheelSpinning = false;
                $('#swn-spin-trigger').prop('disabled', false).css('opacity', 1);
            }
        });
    });


    /**
     * -----------------------------
     * CALLBACKS AFTER SPIN
     * -----------------------------
     */

    // Called when animation ends

    function alertPrize(indicatedSegment) { // indicatedSegment is passed by Winwheel
        wheelSpinning = false;
        $('#swn-spin-trigger').prop('disabled', false).css('opacity', 1);

        if (theWheel.userData) {
            // Show SweetAlert with details
            messageArea.html('<h3>' + 'شما برنده ' + theWheel.userData.prize_name + ' شدید.' + '</h3><p>' + theWheel.userData.prize_details + '</p>').addClass('swn-success');
            let winning_text = (swn_params.win_message || 'You won: %s').replace('%s', indicatedSegment.text) + '<br/>' + theWheel.userData.prize_details;
            Swal.fire({
                icon: "success",
                title: "تبریک",
                html: winning_text,
                confirmButtonText: "تایید",
                didOpen: () => {
                    // Reset and play the sound
                    success_audio.pause();
                    success_audio.currentTime = 0;
                    success_audio.play().catch(err => console.warn('Playback prevented:', err));
                }
            });
            // reset the wheel to allow further spins if chances remain.
            theWheel.stopAnimation(false); // Stop the animation, false as param so callback not called again.
            theWheel.rotationAngle = 0;    // Re-set the wheel angle to 0 degrees.
            theWheel.draw();                // Call draw to render changes to the wheel.
            drawTriangle();                 // Re-draw the pointer.
        } else if (indicatedSegment && indicatedSegment.text) {
            // Fallback if userData wasn't set properly but segment is known
            let winning_text = (swn_params.win_message || 'You won: %s').replace('%s', indicatedSegment.text);
            messageArea.text(winning_text).addClass('swn-success');
            Swal.fire({
                icon: "success",
                title: "تبریک",
                text: 'You won: ' + indicatedSegment.text,
                footer: '<a href="#">what to do now?</a>'
            });
        } else {
            messageArea.text('Spin complete! Prize details unavailable.').addClass('swn-error');
        }

        if (currentSpinChances <= 0) {
            $('#swn-spin-trigger').prop('disabled', true).css('opacity', 0.5);
        }
    }

    // Used if spin animation fails to start
    function alertPrizeDirectly(data) {
        messageArea.html('<h3>' + data.prize.display_name + '</h3><p>' + data.prize_details + '</p>').addClass('swn-success');
        currentSpinChances = parseInt(data.remaining_spins);
        updateSpinChancesDisplay();
        if (currentSpinChances <= 0) {
            $('#swn-spin-trigger').prop('disabled', true).css('opacity', 0.5);
        }
    }


    /**
     * -----------------------------
     * SPIN CHANCES DISPLAY
     * -----------------------------
     */
    function updateSpinChancesDisplay() {
        let chancesText = (swn_params.remaining_spins_text || 'Remaining spin chances: %d').replace('%d', currentSpinChances);

        if (spinChancesDisplay.length) {
            spinChancesDisplay.text(chancesText);
        }
    }

    // Initial spin chances update
    updateSpinChancesDisplay();
    if (currentSpinChances <= 0 && swn_params.user_logged_in) {
        $('#swn-spin-trigger').prop('disabled', true).css('opacity', 0.5);
        // if (messageArea.text().trim() === "") { // Only show if no other message
        //     messageArea.text(swn_params.no_spins_message);
        // }
    }

});