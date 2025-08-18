document.addEventListener('click', function (e) {
    if (e.target.classList.contains('copy-shortcode')) {
        e.preventDefault();
        const shortcode = e.target.getAttribute('data-shortcode');
        navigator.clipboard.writeText(shortcode).then(() => {
            e.target.textContent = swnData.translations.copied;
            setTimeout(() => {
                e.target.textContent = swnData.translations.copy_shortcode;
            }, 2000);
        });
    }
});




// jQuery(document).ready(function ($) {
//     // Prize Settings Page: Add/Remove Prize Segments
//     let prizeContainer = $('#swn-prizes-container');

//     $('#swn-add-prize').on('click', function () {
//         let newIndex = prizeContainer.find('.swn-prize-entry').length;
//         let newEntry = `
//             <div class="swn-prize-entry" data-index="${newIndex}">
//                 <hr>
//                 <h4>Prize Segment <span class="math-inline">\{newIndex \+ 1\}</h4\>
// <p\>
// <label\>Prize Name/Text on Wheel\:</label\><br\>
// <input type\="text" name\="swn\_prizes\_settings\[</span>{newIndex}][name]" value="" required />
//                 </p>
//                 <p>
//                     <label>Prize Type:</label><br>
//                     <select name="swn_prizes_settings[<span class="math-inline">\{newIndex\}\]\[type\]"\>
// <option value\="coupon"\>Discount Coupon</option\>
// <option value\="credit"\>Credit</option\>
// <option value\="product"\>Free Product \(by ID\)</option\>
// <option value\="nothing" selected\>Nothing/Try Again</option\>
// </select\>
// </p\>
// <p\>
// <label\>Prize Value\:</label\><br\>
// <input type\="text" name\="swn\_prizes\_settings\[</span>{newIndex}][value]" value="" />
//                     <small><em>E.g., Coupon Code, Credit Amount, Product ID.</em></small>
//                 </p>
//                 <p>
//                     <label>Probability (%):</label><br>
//                     <input type="number" name="swn_prizes_settings[<span class="math-inline">\{newIndex\}\]\[probability\]" value\="" min\="0" max\="100" step\="1" required /\>
// </p\>
// <p\>
// <label\>Segment Color\:</label\><br\>
// <input type\="color" name\="swn\_prizes\_settings\[</span>{newIndex}][segment_color]" value="#CCCCCC" />
//                 </p>
//                 <button type="button" class="button swn-remove-prize">Remove Prize Segment</button>
//             </div>`;
//         prizeContainer.append(newEntry);
//         renumberPrizes();
//     });

//     prizeContainer.on('click', '.swn-remove-prize', function () {
//         $(this).closest('.swn-prize-entry').remove();
//         renumberPrizes();
//     });

//     function renumberPrizes() {
//         prizeContainer.find('.swn-prize-entry').each(function (i) {
//             $(this).attr('data-index', i);
//             $(this).find('h4').text(`Prize Segment ${i + 1}`);
//             $(this).find('input, select').each(function () {
//                 let name = $(this).attr('name');
//                 if (name) {
//                     name = name.replace(/\[\d+\]/, `[${i}]`);
//                     $(this).attr('name', name);
//                 }
//             });
//         });
//     }


//     // Manual Spins Page
//     const manualSpinMessage = $('#swn-manual-spin-message');
//     const currentUserSpinsDisplay = $('#swn-current-user-spins-display');

//     $('#