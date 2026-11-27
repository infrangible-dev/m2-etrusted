/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'slick'
], function ($) {
    'use strict';

    /**
     * Shows the reviews of the element in a slider.
     *
     * @param {Object} config options of slick that replace the defaults, for example {"autoplaySpeed": 6000}
     * @param {HTMLElement} element
     */
    return function (config, element) {
        $(element).slick($.extend({
            autoplay: true,
            autoplaySpeed: 4000,
            arrows: false,
            dots: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            speed: 2000
        }, config));
    };
});
