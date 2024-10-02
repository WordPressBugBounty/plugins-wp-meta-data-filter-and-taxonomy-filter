"use strict";

jQuery(function () {
    jQuery('.mdf-slide-out-div').css('height', 'auto');
    jQuery('.mdf-slide-out-div').css('opacity', 1);
    jQuery('.mdf-slide-out-div').tabSlideOut({
        tabHandle: '.mdf-handle', //class of the element that will be your tab
        pathToTabImage: tab_slideout_icon, //link to the image for the tab *required*
        imageHeight: tab_slideout_icon_h + 'px', //height of tab image *required*
        imageWidth: tab_slideout_icon_w + 'px', //width of tab image *required*    
        tabLocation: jQuery('.mdf-slide-out-div').data('location'), //side of screen where tab lives, top, right, bottom, or left
        speed: jQuery('.mdf-slide-out-div').data('speed'), //speed of animation
        action: jQuery('.mdf-slide-out-div').data('action'), //options: 'click' or 'hover', action to trigger animation
        topPos: jQuery('.mdf-slide-out-div').data('toppos') + 'px', //position from the top
        fixedPosition: parseInt(jQuery('.mdf-slide-out-div').data('fixedposition'), 10) == 1 ? true : false, //options: true makes it stick(fixed position) on scroll
        onLoadSlideOut: parseInt(jQuery('.mdf-slide-out-div').data('onloadslideout'), 10)
    });
});




