/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

require(['jquery', 'jquery/ui', 'slick'], function($){
    $(document).ready(function() {
        $('.etrusted-reviews').slick({
            "autoplay": true,
            "autoplaySpeed": 4000,
            "arrows": false,
            "dots": true,
            "slidesToShow": 1,
            "slidesToScroll": 1,
            "speed": 2000
        });
    });
});
