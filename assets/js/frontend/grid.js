/**
 * Created by g5theme on 12/14/2016.
 */
var GridPlus = GridPlus || {};
(function ($) {
    "use strict";
    
    // Ensure jQuery is properly available in WordPress context
    if (typeof jQuery === 'undefined') {
        console.error('[GridPlus] jQuery is not available!');
        return;
    }
    
    // Store jQuery reference for Light Gallery
    window.GridPlusJQuery = $;
    GridPlus = {
        vars: {
            grid_on_ajax: false
        },
        originalPositions: {}, // Store original positions for each grid
        init: function () {
            GridPlus.galleries = [];
            GridPlus.items = [];
            GridPlus.pagings = [];
            
            // Fix for Light Gallery jQuery compatibility
            if (typeof $.fn.lightGallery !== 'undefined' && !$.fn.lightGallery._jQueryFixed) {
                var originalLG = $.fn.lightGallery;
                $.fn.lightGallery = function(options) {
                    // Ensure this is a jQuery object
                    var $this = $(this);
                    return originalLG.call($this, options);
                };
                $.fn.lightGallery._jQueryFixed = true;
            }
            
            GridPlus.initHashNavigation(); // Move this to the beginning
            GridPlus.initGrid();
            GridPlus.initCarousel();
            GridPlus.execCategoryFilter();
            GridPlus.initCateExpanded();
            GridPlus.initFilterByCategory();
            GridPlus.initPaging();
            GridPlus.initWindowScroll();
            GridPlus.initViewGallery();
            GridPlus.initAddToCartAjax();
            GridPlus.initSearch();
            
            // Ensure event handlers remain bound after dynamic content changes
            $(document).on('onAfterClose.lg', function() {
                // Clear any lingering state
                window.gridPlusUrlModule = null;
                window.gridPlusForceModule = null;
                $('.grid-post-item').removeClass('active');
                
                // If we came from a direct URL and haven't clicked yet, reinit to ensure handlers work
                if (window.gridPlusFromDirectUrl) {
                    window.gridPlusFromDirectUrl = false;
                    
                    // Ensure handlers are properly bound
                    setTimeout(function() {
                        // Force rebind of event delegation handlers
                        $(document).off('mousedown.urlupdate').on('mousedown.urlupdate', 'a.view-gallery', function(e) {
                            var $this = $(this);
                            var $post_item = $this.closest('.grid-post-item');
                            var moduleIndex = $('.grid-post-item').index($post_item) + 1;
                            
                            if (history.pushState && moduleIndex) {
                                var newUrl = '/discover/' + moduleIndex;
                                history.pushState({module: moduleIndex}, '', newUrl);
                            }
                        });
                    }, 100);
                }
            });
        },
        initGrid: function ($container) {
            if(typeof($container) == 'undefined') {
                $container = $('body');
            }
            $('.grid-stack-container', $container).each(function () {
                var $section_id = $(this).attr('data-section-id'),
                    $gutter = $(this).attr('data-gutter'),
                    $grid_container = $('#' + $section_id),
                    $fix_item_height = $(this).attr('data-fix-item-height'),
                    $layout_type = $(this).attr('data-layout-type'),
                    $grid_stack = $('.grid-stack', $grid_container),
                    $grid_stack_container = $('.grid-stack-container', $grid_container),
                    $height_ratio = $grid_stack_container.attr('data-height-ratio'),
                    $width_ratio = $grid_stack_container.attr('data-width-ratio'),
                    $column = parseInt($grid_stack_container.attr('data-columns')),
                    $items = $('.grid-items .grid-post-item', $grid_container),
                    $cellHeight = $layout_type == 'grid' ? 0 : 10,
                    $grid_items = [],
                    $el = null,
                    $grid = null,
                    $grid_stack_item = null,
                    $div_thumbnail = null,
                    $data_img = null,
                    $index = 0,
                    $post_info_height = 0,
                    $masonry_height = 0,
                    $image_width = 0,
                    $image_height = 0,
                    $percent = 0,
                    $container_width = $grid_container.width(),
                    $item_width = Math.ceil($container_width/$column),
                    $item_height = 0;

                var options = {
                    cellHeight: $cellHeight,
                    verticalMargin: $gutter,
                    animate: true,
                    acceptWidgets: true,
                    removable: false,
                    disableDrag: true,
                    disableResize: true
                };
                // Initialize original positions if not already set
                if (!GridPlus.originalPositions[$section_id]) {
                    GridPlus.originalPositions[$section_id] = {};
                    $('.grid-items .grid-post-item', $grid_container).each(function(idx) {
                        var $link = $(this).find('a.view-gallery');
                        var postId = $link.attr('data-post-id');
                        if (postId) {
                            GridPlus.originalPositions[$section_id][postId] = idx + 1;
                        }
                    });
                }
                
                $('.grid-items .item', $grid_container).each(function ($index) {
                    $grid_items.push({
                        x: $(this).attr('data-gs-x'),
                        y: $(this).attr('data-gs-y'),
                        width: $(this).attr('data-gs-width'),
                        height: $(this).attr('data-gs-height'),
                        desktop_x: $(this).attr('data-desktop-gs-x'),
                        desktop_y: $(this).attr('data-desktop-gs-y'),
                        desktop_width: $(this).attr('data-desktop-gs-width'),
                        desktop_height: $(this).attr('data-desktop-gs-height'),
                        image_height: $(this).attr('data-image-height'),
                        image_width: $(this).attr('data-image-width'),
                        original_index: $(this).attr('data-original-index')
                    });
                });

                $grid_stack.attr('data-gutter', 'gutter-' + $gutter);
                $grid_stack.gridstack(options);
                $grid = $grid_stack.data('gridstack');
                _.each($grid_items, function (node) {
                    $el = $grid.addWidget($('<div><div class="grid-stack-item-content" /><div/>'),
                        node.x, node.y, node.width, node.height);

                    $($el).attr('data-desktop-gs-x', node.desktop_x);
                    $($el).attr('data-desktop-gs-y', node.desktop_y);
                    $($el).attr('data-desktop-gs-width', node.desktop_width);
                    $($el).attr('data-desktop-gs-height', node.desktop_height);
                    $($el).attr('data-image-height', node.image_height);
                    $($el).attr('data-image-width', node.image_width);

                    $grid_stack_item = $('.grid-stack-item-content', $el);

                    $grid_stack_item.append($items[$index++]);
                    if ($index == $items.length) {
                        $index = 0;
                    }
                    
                    // Transfer original-index to the grid-post-item
                    if (node.original_index) {
                        $('.grid-post-item', $grid_stack_item).attr('data-original-index', node.original_index);
                    }

                    $div_thumbnail = $('.thumbnail-image', $grid_stack_item);
                    $data_img = $div_thumbnail.attr('data-img');
                    if (typeof $data_img != 'undefined' && $data_img != null && $data_img != '') {
                        $div_thumbnail.css('background-image', 'url(' + $data_img + ')');
                        $('img', $div_thumbnail).remove();
                    }

                    $item_width = $('.grid-post-item', this).outerWidth(true);
                    if ($layout_type == 'grid') {
                        if ($fix_item_height == 'true') {
                            var $post_info_class = $('.grid-post-item', $grid_stack_item).attr('data-post-info-class');
                            $post_info_height = $('.grid-post-item .' + $post_info_class, $grid_stack_item).outerHeight(true);
                        }
                        $item_height = Math.floor( $item_width * ($height_ratio/$width_ratio) );
                        $div_thumbnail.css('height',$item_height + 'px');
                    } else {
                        if ($('.grid-post-item', $grid_stack_item).attr('data-thumbnail-only') == '1') {
                            $image_width = parseInt($($el).attr('data-image-width'));
                            $image_height = parseInt($($el).attr('data-image-height'));
                            $percent = $masonry_height = 0;
                            if($image_width >= $item_width){
                                $percent = $image_width/$item_width;
                                $masonry_height = Math.floor($image_height / $percent);
                            }
                            if ($masonry_height > 0) {
                                //for masonry layout
                                $div_thumbnail.css('height', $masonry_height + 'px');
                                $('.grid-post-item', $grid_stack_item).css('height', $masonry_height + 'px');
                            } else {
                                $div_thumbnail.css('height', '100%');
                                $('.grid-post-item', $grid_stack_item).css('height', '100%');
                            }

                        } else {
                            if ($fix_item_height == 'true') {
                                var $post_info_class = $('.grid-post-item', $grid_stack_item).attr('data-post-info-class');
                                $post_info_height = $('.grid-post-item .' + $post_info_class, $grid_stack_item).outerHeight(true);
                            }
                            $image_width = parseInt($($el).attr('data-image-width'));
                            $image_height = parseInt($($el).attr('data-image-height'));
                            $percent = $masonry_height = 0;
                            if($image_width >= $item_width){
                                $percent = $image_width/$item_width;
                                $masonry_height = Math.floor($image_height / $percent);
                            }
                            if ($masonry_height > 0) {
                                $div_thumbnail.css('height', $masonry_height + 'px');
                            } else {
                                $div_thumbnail.css('height', (node.height * 10 - $post_info_height) + 'px');
                            }
                            $('.grid-post-item', $grid_stack_item).css('height', 'auto');
                        }
                    }
                }, this);
                GridPlus.responsive(function () {
                    $grid_container.trigger('gridInitCompleted');
                    setTimeout(function () {
                        GridPlus.itemAnimation($grid_container);
                        GridPlus.preloadPostTags(); // Preload tags after grid is initialized
                    }, 300);
                })
            });
        },

        initCarousel: function($container){
            if(typeof($container) == 'undefined') {
                $container = $('body');
            }
            $('.grid-owl-carousel', $container).each(function(){
                var $owl = $(this),
                    defaults = {
                        items: 4,
                        nav: false,
                        navText: ['<i class="fa fa-angle-left"></i> ', ' <i class="fa fa-angle-right"></i>'],
                        dots: false,
                        loop: true,
                        center: false,
                        mouseDrag: true,
                        touchDrag: true,
                        pullDrag: true,
                        freeDrag: false,
                        margin: 0,
                        stagePadding: 0,
                        merge: false,
                        mergeFit: true,
                        autoWidth: false,
                        startPosition: 0,
                        rtl: false,
                        smartSpeed: 250,
                        autoplay: false,
                        autoplayTimeout: 0,
                        fluidSpeed: false,
                        dragEndSpeed: false,
                        autoplayHoverPause: true,
                        onResized: GridPlus.initResizeCarousel,
                        onInitialized: GridPlus.initCarouselInitialized
                    },
                    nav_next_text = $owl.data("nav-next-text"),
                    nav_prev_text = $owl.data("nav-prev-text");
                if(typeof (nav_next_text) !== 'undefined' && typeof (nav_prev_text) !== 'undefined') {
                    defaults.navText = [nav_prev_text, nav_next_text];
                }
                var config = $.extend({}, defaults, $owl.data("owl-options"));
                // Initialize Slider
                $owl.gridOwlCarousel(config);
            });

            $('.carousel-container', $container).each(function(){
                var $container = $(this),
                    $animation = $container.parent().attr('data-animation'),
                    $height_ratio = $container.attr('data-height-ratio'),
                    $width_ratio = $container.attr('data-width-ratio'),
                    $items_animation = [];

                $('.item', $container).each(function(){
                    var $div_thumbnail = $('.thumbnail-image', $(this)),
                        $data_img = $div_thumbnail.attr('data-img'),
                        $width = parseInt($div_thumbnail.width()),
                        $height;
                    if($('img', $div_thumbnail).length > 0){
                        $('img', $div_thumbnail).remove();
                    }
                    if(typeof $data_img !='undefined' && $data_img !=''){
                        $div_thumbnail.css('background-image', 'url(' + $data_img + ')');
                    }
                    $height = Math.floor( parseInt($height_ratio) / parseInt($width_ratio) * $width );
                    $div_thumbnail.css('height', $height + 'px');
                    $items_animation.push($(this));
                });
                for (var $i = 0; $i < $items_animation.length; $i++) {
                    (function ($index) {
                        var $delay = 100 * $i;
                        setTimeout(function () {
                            $($items_animation[$index]).addClass('animated ' + $animation);
                            $($items_animation[$index]).css('opacity', 1);

                        }, $delay);
                    })($i);
                }
            });
        },

        itemAnimation: function ($container) {
            var $items = $('.grid-stack-item-content', $container),
                $animation = $container.attr('data-animation');
            for (var $i = 0; $i < $items.length; $i++) {
                if(GridPlus.isAppear($items[$i])){
                    (function ($index) {
                        var $delay = 100 * $i;
                        setTimeout(function () {
                            $($items[$index]).addClass('animated ' + $animation);
                            $($items[$index]).addClass('infinited');
                            $($items[$index]).css('opacity', 1);
                            if (($index + 1) == $items.length) {
                                $container.trigger('itemAnimationCompleted');
                            }
                        }, $delay);
                    })($i);
                }else{
                    $($items[$i]).addClass('has-infinite');
                    $($items[$i]).attr('data-animation', $animation);
                }
            }
        },

        initResize: function ($container, callback) {
            var $rows = [],
                $data_gs_y = '',
                $previous_height = 0,
                $max_height = 0,
                $top = 0,
                $container_height = 0,
                $gutter = $('.grid-stack-container', $container).attr('data-gutter'),
                $item_width = 0,
                $item_height = 0,
                $height_ratio = $('.grid-stack-container', $container).attr('data-height-ratio'),
                $width_ratio = $('.grid-stack-container', $container).attr('data-width-ratio');

            $('.grid-stack-item', $container).each(function () {
                $data_gs_y = $(this).attr('data-gs-y');
                $max_height = 0;

                if($('.grid-post-item', this).attr('data-thumbnail-only') == '1'){
                    $item_width = $('.grid-post-item', this).outerWidth(false);
                    $item_height = Math.floor( $item_width * ($height_ratio/$width_ratio) );
                    $('.grid-post-item', this).css('height', $item_height + 'px');
                    $('.grid-post-item > div', this).css('height', $item_height + 'px');
                }else{
                    /*$item_width = $('.grid-post-item', this).outerWidth(true);
                     $('.grid-post-item div.thumbnail-image', this).css('height', Math.floor( $item_width * ($height_ratio/$width_ratio) ) + 'px' );*/
                    $item_height =  $('.grid-post-item', this).outerHeight(true);
                }
                if(!$('.grid-stack', $container).hasClass('grid-stack-one-column-mode')){
                    if (_.indexOf($rows, $data_gs_y) == -1) {
                        $('.grid-stack-item[data-gs-y="' + $data_gs_y + '"]', $container).each(function () {
                            if ($item_height > $max_height) {
                                $max_height = $item_height;
                            }
                        });
                        $rows.push($data_gs_y);
                        if ($rows.length > 1) {
                            $top += parseInt($previous_height) + parseInt($gutter);
                        }
                        $previous_height = $max_height;
                        $container_height += parseInt($max_height) + parseInt($gutter);

                        $('.grid-stack-item[data-gs-y="' + $data_gs_y + '"]', $container).css('height', ($max_height + 30) + 'px');
                        if (!isNaN($top)) {
                            $('.grid-stack-item[data-gs-y="' + $data_gs_y + '"]', $container).css('top', $top);
                        }
                    }
                }else{
                    $top += parseInt($previous_height) + parseInt($gutter);
                    $previous_height = $item_height;
                    $container_height += parseInt($item_height) + parseInt($gutter);
                    $top += parseInt($previous_height) + parseInt($gutter);

                    $('.grid-stack-item[data-gs-y="' + $data_gs_y + '"]', $container).css('height', $item_height + 'px');
                    if (!isNaN($top)) {
                        $('.grid-stack-item[data-gs-y="' + $data_gs_y + '"]', $container).css('top', $top);
                    }
                }
            });
            if (!isNaN($container_height)) {
                $('.grid-stack', $container).css('height', $container_height);
            }
            if (callback) {
                callback($container);
            }
        },

        initResizeMasonry: function ($container, $force_position_top, callback) {
            var $rows = [],
                $data_gs_y = 0,
                $data_gs_x = 0,
                $data_gs_width = 0,
                $data_gs_height = 0,
                $height = 0,
                $current_top = 0,
                $current_left = 0,
                $container_height = 0,
                $insert_to_col = 1,
                $gutter = parseInt($('.grid-stack-container', $container).attr('data-gutter')),
                $column = parseInt($('.grid-stack-container', $container).attr('data-columns')),
                $container_width = $('.grid-stack', $container).width(),
                $grid_items = {},
                $min_height = 0,
                $current_width = Math.ceil($container_width/$column);

            for(var $i=1; $i<=$column; $i++){
                $grid_items['col_' + $i] = {'height': 0};
            }

            $force_position_top = typeof $force_position_top !== 'undefined' ? $force_position_top : false;
            $('.grid-stack-item', $container).each(function () {
                $data_gs_y = parseInt($(this).attr('data-gs-y'));
                $data_gs_x = parseInt($(this).attr('data-gs-x'));
                $data_gs_width = parseInt($(this).attr('data-gs-width'));
                $data_gs_height = parseInt($(this).attr('data-gs-height'));
                $height = parseInt($('.grid-post-item', this).outerHeight(true));
                $(this).css('height', $height);
                $min_height = 0;

                for(var $i=1; $i<=$column;$i++){
                    if($grid_items['col_' + $i].height==0){
                        $insert_to_col = $i;
                        $min_height = 0;
                        $current_left = 0;
                        break;
                    }else{
                        if($min_height==0 || $min_height > $grid_items['col_' + $i].height){
                            $min_height = $grid_items['col_' + $i].height;
                            $insert_to_col = $i;
                        }
                    }
                }

                $current_top = $min_height;
                $current_left = ($insert_to_col-1) * $current_width;

                $grid_items['col_' + $insert_to_col].height = $grid_items['col_' + $insert_to_col].height + $height   + $gutter;

                $(this).css('top', $current_top);
                $(this).css('left', $current_left);

                if (($current_top + $height + $gutter) > $container_height) {
                    $container_height = $current_top + $height + $gutter;
                }
            });
            if (!isNaN($container_height)) {
                $('.grid-stack', $container).css('height', $container_height);
            }
            if (callback) {
                callback($container);
            }

        },

        initResizeCarousel: function(){
            $('.carousel-container').each(function(){
                var $container = $(this),
                    $height_ratio = $container.attr('data-height-ratio'),
                    $width_ratio = $container.attr('data-width-ratio');

                $('.item', $container).each(function(){
                    var $div_thumbnail = $('.thumbnail-image', $(this)),
                        $width = parseInt($div_thumbnail.width()),
                        $height;
                    $height = Math.floor( parseInt($height_ratio) / parseInt($width_ratio) * $width );
                    $div_thumbnail.css('height', $height + 'px');
                });
            });
        },

        initCarouselInitialized: function(event){
            $('.carousel-items',event.target).css('min-height','auto');
            /*setTimeout(function(){
             $('.grid-owl-dots, .grid-owl-nav', event.target).css('opacity',1);
             var $data_show_nav = $(event.target).attr('data-show-nav'),
             $data_show_dot = $(event.target).attr('data-show-dot');
             if(typeof $data_show_dot !='undefined' && $data_show_dot=='1'){
             $('.grid-owl-dots',event.target).removeClass('disabled');
             }
             if(typeof $data_show_nav !='undefined' && $data_show_nav=='1'){
             $('.grid-owl-nav',event.target).removeClass('disabled');
             }
             },500);*/
        },

        find: function ($rows, $x, $y) {
            _.find($rows, function ($item) {
                return $item.x == $x && $item.y == $y;
            })
        },

        findPreviousNode: function ($rows, $x, $y, $data_gs_width) {
            var $height = 0,
                $item = null;
            for (var $i = 0; $i < $rows.length; $i++) {
                if ($rows[$i].y < $y) {
                    if (
                        ( $rows[$i].x > $x && $rows[$i].x >= ( $x + $data_gs_width) )
                        || ( $rows[$i].x < $x && ($rows[$i].x + $rows[$i].gs_width ) <= $x )
                    ) {
                    } else {
                        if (($rows[$i].height + $rows[$i].top) > $height) {
                            $height = $rows[$i].height + $rows[$i].top;
                            $item = $rows[$i];
                        }
                    }
                }
            }
            return $item;
        },

        responsive: function (callback) {
            $('.grid-plus-container').each(function () {
                var $this = $(this),
                    $grid_stack_container = $('.grid-stack-container',$this),
                    $layout_type = $grid_stack_container.attr('data-layout-type'),
                    $index = 1,
                    $data_gs_y = 0,
                    $arr_gs_y = [];
                if(typeof $layout_type=='undefined'){
                    return;
                }
                if (window.matchMedia('(min-width: 992px)').matches) {
                    $grid_stack_container.attr('data-columns', $grid_stack_container.attr('data-desktop-columns'));
                    $('.grid-stack-item', $this).each(function () {
                        $(this).attr('data-gs-y', $(this).attr('data-desktop-gs-y'));
                        $(this).attr('data-gs-x', $(this).attr('data-desktop-gs-x'));
                        $(this).attr('data-gs-width', $(this).attr('data-desktop-gs-width'));
                        $(this).attr('data-gs-height', $(this).attr('data-desktop-gs-height'));
                    });
                } else {
                    if (window.matchMedia('(min-width: 600px)').matches) {
                        $('.grid-stack', $this).removeClass('grid-stack-one-column-mode');
                        $grid_stack_container.attr('data-columns', $grid_stack_container.attr('data-tablet-columns'));
                        $('.grid-stack-item', $this).each(function () {
                            $(this).attr('data-gs-width', '6');
                            if ($index % 2 == 0) {
                                $(this).attr('data-gs-x', '6');
                                if (typeof $arr_gs_y[$index - 3] != 'undefined') {
                                    $(this).attr('data-gs-y', $arr_gs_y[$index - 3]);
                                }
                            }
                            if ($index % 2 == 1) {
                                $(this).attr('data-gs-x', '0');
                                if (typeof $arr_gs_y[$index - 2] != 'undefined') {
                                    $(this).attr('data-gs-y', $arr_gs_y[$index - 2]);
                                }
                            }
                            $data_gs_y = parseInt($(this).attr('data-gs-y')) + parseInt($(this).attr('data-gs-height'));
                            $arr_gs_y.push($data_gs_y);
                            $index++;
                        })
                    } else {
                        if (!$('.grid-stack', $this).hasClass('grid-stack-one-column-mode')) {
                            $('.grid-stack', $this).addClass('grid-stack-one-column-mode');
                        }
                        $grid_stack_container.attr('data-columns', $grid_stack_container.attr('data-mobile-columns'));
                    }
                }
                setTimeout(function () {
                    if ($layout_type == 'metro') {
                        return;
                    } else if($layout_type == 'masonry') {
                        GridPlus.initResizeMasonry($this, true, callback);
                    }else{
                        GridPlus.initResize($this, callback);
                    }
                }, 300);
            });
        },
        initCateExpanded: function () {
            $(document).on('click', function (e) {
                var hasActive = false;
                if($(e.target).closest('.grid-cate-expanded').length > 0
                    && $(e.target).hasClass('grid-dropdown-toggle')
                    && $(e.target).closest('.grid-cate-expanded').find('.grid-dropdown-menu').hasClass('active')) {
                    hasActive = true;
                }
                $('.grid-dropdown-menu', '.grid-cate-expanded').removeClass('active');
                if(!hasActive) {
                    $(e.target).closest('.grid-cate-expanded').find('.grid-dropdown-menu').addClass('active');
                }
            });
        },
        initFilterByCategory: function () {
            $('a', '.grid-category').off('click').on('click', function () {
                if(!GridPlus.vars.grid_on_ajax && !$(this).hasClass('active')) {
                    GridPlus.vars.grid_on_ajax = true;
                    var $grid_plus_inner = $(this).closest('.grid-plus-inner'),
                        $section_id = $grid_plus_inner.attr('data-section-id'),
                        $layout = $grid_plus_inner.attr('data-layout-type'),
                        $ajax_url = $grid_plus_inner.attr('data-ajax-url'),
                        $nonce = $grid_plus_inner.attr('data-nonce'),
                        $category_id = $(this).attr('data-category');
                    if ($layout == 'carousel') {
                        GridPlus.filterCarousel(this, $section_id, $ajax_url, $category_id, 1, $nonce);
                    } else{
                        GridPlus.filter(this, $section_id, $ajax_url, $category_id, 1, 1, $nonce);
                    }
                    $('.grid-category a', $grid_plus_inner).removeClass('active');
                    $(this).addClass('active');
                }
            });
        },

        //$filter_type = 1 -> Category filter, = 2 -> Paging filter, = 3 -> Load more filter, = 4 -> Infinite scroll
        filter: function (elm, $section_id, $ajax_url, $category_id, $current_page, $filter_type, $nonce) {
            var $container = $('#' + $section_id);
            
            // Prevent loading more items during search
            if ($container.hasClass('search-active')) {
                GridPlus.vars.grid_on_ajax = false;
                return false;
            }
            
            var $grid_name = $container.attr('data-grid-name'),
                $ladda = null,
                $this = $(elm),
                $key = '',
                $items = null,
                $paging = null,
                $paging_wrap_class = '';
            $filter_type = typeof $filter_type != 'undefined' ? $filter_type : 1;

            if($this.closest('.grid-cate-expanded').length > 0) {
                $this.closest('.grid-cate-expanded').children('ul').addClass('active');
            }
            if ($this.hasClass('ladda-button')) {
                $ladda = Ladda.create(elm);
                $ladda.start();
            }

            $current_page = (typeof $current_page != 'undefined') ? $current_page : 1;

            $key = $section_id + '_' + $category_id + '_' + $current_page;

            if (typeof GridPlus.items[$key] == 'undefined') {
                $.ajax({
                    url: $ajax_url,
                    type: 'POST',
                    data: ({
                        action: 'grid_plus_load_by_category',
                        category_id: $category_id,
                        current_page: $current_page,
                        grid_name: $grid_name,
                        nonce: $nonce
                    }),
                    success: function (data) {
                        if ($ladda != null) {
                            $ladda.stop();
                        }

                        $items = $('.grid-items .item', data);

                        GridPlus.items[$key] = $items.clone(true);

                        $('div[data-section-id="' + $section_id + '"]').attr('data-current-category', $category_id);

                        if ($filter_type == 3 || $filter_type == 4) {
                            $paging = $filter_type == 3 ? $('a.load-more', data) : $('a.infinite-scroll', data);
                            $paging_wrap_class = $filter_type == 3 ? '.grid-load-more-wrap' : '.grid-infinite-scroll-wrap';
                            GridPlus.pagings[$key] = $paging.clone(true);
                            GridPlus.appendItem($container, $items, $paging, $paging_wrap_class);
                        } else {
                            $paging = $('.grid-paging-navigation', data);

                            if ($paging.length <= 0){
                                //for click category filter with load more
                                if($('a.load-more', data).length > 0) {
                                    $paging = $('a.load-more', data);
                                }
                                //for click category filter with infinite scroll
                                if ($('a.infinite-scroll', data).length > 0) {
                                    $paging = $('a.infinite-scroll', data);
                                }
                            }
                            GridPlus.pagings[$key] = $paging.clone(true);
                            GridPlus.bindItem($container, $items, $paging, $filter_type);
                        }

                        GridPlus.initViewGallery($container);
                        GridPlus.initAddToCartAjax($container);
                        GridPlus.preloadPostTags(); // Preload tags for new items
                        GridPlus.vars.grid_on_ajax = false;
                        if($this.closest('.grid-cate-expanded').length > 0) {
                            $this.closest('.grid-cate-expanded').children('ul').removeClass('active');
                        }
                    },
                    error: function () {
                        if ($ladda != null) {
                            $ladda.stop();
                        }
                        GridPlus.vars.grid_on_ajax = false;
                        if($this.closest('.grid-cate-expanded').length > 0) {
                            $this.closest('.grid-cate-expanded').children('ul').removeClass('active');
                        }
                        return false;
                    }
                });
            } else {
                if ($ladda != null) {
                    $ladda.stop();
                }
                $('div[data-section-id="' + $section_id + '"]').attr('data-current-category', $category_id);
                $items = GridPlus.items[$key].clone(true);

                if ($filter_type == 3 || $filter_type == 4) {
                    $paging = GridPlus.pagings[$key].clone(true);
                    $paging_wrap_class = $filter_type == 3 ? '.grid-load-more-wrap' : '.grid-infinite-scroll-wrap';
                    GridPlus.appendItem($container, $items, $paging, $paging_wrap_class);
                } else {
                    $paging = GridPlus.pagings[$key].clone(true);
                    GridPlus.bindItem($container, $items, $paging, $filter_type);
                }

                GridPlus.initViewGallery($container);
                GridPlus.initAddToCartAjax($container);
                GridPlus.vars.grid_on_ajax = false;
                if($this.closest('.grid-cate-expanded').length > 0) {
                    $this.closest('.grid-cate-expanded').children('ul').removeClass('active');
                }
            }
        },

        filterCarousel: function(elm, $section_id, $ajax_url, $category_id , $filter_type, $nonce){
            var $container = $('#' + $section_id),
                $grid_name = $container.attr('data-grid-name'),
                $ladda = null,
                $this = $(elm),
                $key = $section_id + '_' + $category_id,
                $items = null;
            if ($this.hasClass('ladda-button')) {
                $ladda = Ladda.create(elm);
                $ladda.start();
            }

            if (typeof GridPlus.items[$key] == 'undefined') {
                $.ajax({
                    url: $ajax_url,
                    type: 'POST',
                    data: ({
                        action: 'grid_plus_load_by_category',
                        category_id: $category_id,
                        current_page: 1,
                        grid_name: $grid_name,
                        nonce: $nonce
                    }),
                    success: function (data) {
                        if ($ladda != null) {
                            $ladda.stop();
                        }

                        $items = $('.carousel-items .item', data);
                        GridPlus.items[$key] = $items.clone(true);
                        $('div[data-section-id="' + $section_id + '"]').attr('data-current-category', $category_id);
                        GridPlus.bindItemCarousel($container, $items, $filter_type);
                        GridPlus.initViewGallery($container);
                        GridPlus.initAddToCartAjax($container);
                        GridPlus.preloadPostTags(); // Preload tags for new items
                        GridPlus.vars.grid_on_ajax = false;
                    },
                    error: function () {
                        if ($ladda != null) {
                            $ladda.stop();
                        }
                        GridPlus.vars.grid_on_ajax = false;
                        return;
                    }
                });
            }else{
                if ($ladda != null) {
                    $ladda.stop();
                }
                $('div[data-section-id="' + $section_id + '"]').attr('data-current-category', $category_id);
                $items = GridPlus.items[$key].clone(true);
                GridPlus.bindItemCarousel($container, $items, $filter_type);
                GridPlus.initViewGallery($container);
                GridPlus.initAddToCartAjax($container);
                GridPlus.vars.grid_on_ajax = false;
            }
        },

        bindItem: function ($container, $items, $paging, $filter_type) {
            if(typeof  $('.grid-stack', $container).data('gridstack') !='undefined'){
                $('.grid-stack', $container).data('gridstack').destroy(false);
            }
            $('.grid-stack', $container).empty();
            $('.grid-items', $container).empty();
            $('.grid-items', $container).append($items);
            GridPlus.initGrid($container);

            $('.grid-paging-navigation-wrap .grid-paging-navigation', $container).empty();
            $('.grid-load-more-wrap', $container).empty();
            $('.grid-infinite-scroll-wrap', $container).empty();

            //scroll to top with paging filter
            if ($filter_type == 1 || $filter_type == 2) {
                var $offset = $container.offset().top;
                if ($('#wpadminbar').length > 0) {
                    $offset -= $('#wpadminbar').outerHeight(true);
                }
                $('html,body').animate({scrollTop: $offset + 'px'}, 500);
            }
            if($paging.length > 0){
                setTimeout(function () {
                    if($('.grid-paging-navigation-wrap', $container).length > 0){
                        $('.grid-paging-navigation-wrap .grid-paging-navigation', $container).append($paging.children());
                        $('.grid-paging-navigation-wrap', $container).fadeIn();
                    } else {
                        if ($('.grid-load-more-wrap', $container).length > 0) {
                            $('.grid-load-more-wrap', $container).append($paging);
                        } else if ($('.grid-infinite-scroll-wrap', $container).length > 0) {
                            $('.grid-infinite-scroll-wrap', $container).append($paging);
                        }
                    }
                    GridPlus.initPaging($container);
                },100);
            }
        },

        appendItem: function ($container, $items, $load_more, $paging_wrap_class) {
            var $total_items_display = $('.grid-stack-item', $container).length,
                $grid_items = $('.grid-items .item', $container),
                $total_item_grid = $grid_items.length,
                $start_index = 0,
                $total_has_change = 0,
                $data_gs_y = 0,
                $data_gs_x = 0,
                $data_gs_width = 0,
                $data_gs_height = 0,
                $next_data_gs_y = 0;

            if ($total_items_display < $total_item_grid) {
                $start_index = $total_item_grid - $total_items_display;
            }

            $('.grid-stack-item', $container).each(function () {
                if (parseInt($(this).attr('data-gs-y')) + parseInt($(this).attr('data-gs-height')) > $next_data_gs_y) {
                    $next_data_gs_y = parseInt($(this).attr('data-gs-y')) + parseInt($(this).attr('data-gs-height'));
                }
            });
            for (var $i = $start_index; $i < $total_item_grid; $i++) {
                if ($i == $total_item_grid && $total_has_change <= $items.length) {
                    $i = 0;
                }
                $data_gs_y = parseInt($($grid_items[$i]).attr('data-gs-y')) + $next_data_gs_y;
                $data_gs_x = parseInt($($grid_items[$i]).attr('data-gs-x'));
                $data_gs_width = parseInt($($grid_items[$i]).attr('data-gs-width'));
                $data_gs_height = parseInt($($grid_items[$i]).attr('data-gs-height'));

                $($items[$total_has_change]).attr('data-gs-y', $data_gs_y);
                $($items[$total_has_change]).attr('data-gs-x', $data_gs_x);
                $($items[$total_has_change]).attr('data-gs-width', $data_gs_width);
                $($items[$total_has_change]).attr('data-gs-height', $data_gs_height);

                $($items[$total_has_change]).attr('data-desktop-gs-y', $data_gs_y);
                $($items[$total_has_change]).attr('data-desktop-gs-x', $data_gs_x);
                $($items[$total_has_change]).attr('data-desktop-gs-width', $data_gs_width);
                $($items[$total_has_change]).attr('data-desktop-gs-height', $data_gs_height);

                $total_has_change++;
            }

            $('.grid-items', $container).empty();
            $('.grid-items', $container).append($items);
            GridPlus.initGrid($container);

            $($paging_wrap_class, $container).empty();
            if ($load_more.length > 0) {
                $($paging_wrap_class, $container).append($load_more);
                GridPlus.initPaging($container);
            }
        },

        bindItemCarousel: function($container, $items, $filter_type){
            var $carousel_height = $('.carousel-items',$container).height();
            //$('.grid-owl-dots, .grid-owl-nav', event.target).css('opacity',0);
            $('.carousel-items',$container).trigger('destroy.owl.carousel');
            $('.carousel-items',$container).css('min-height',$carousel_height + 'px');
            $('.carousel-items',$container).empty();
            $('.carousel-items',$container).append($items);
            GridPlus.initCarousel($container);
            //scroll to top with paging filter
            var $offset = $container.offset().top;
            if ($('#wpadminbar').length > 0) {
                $offset -= $('#wpadminbar').outerHeight(true);
            }
            $('html,body').animate({scrollTop: $offset + 'px'}, 500);
        },

        initPaging: function ($container) {
            if(typeof($container) == 'undefined') {
                $container = $('body');
            }
            $('a.page-numbers', $container).each(function () {
                var $this = $(this);
                $this.removeClass('ladda-button').addClass('ladda-button');
                $this.attr('data-style', 'zoom-in');
                $this.attr('data-spinner-color', '#868686');
                $this.attr('data-spinner-size', '20');
            });
            $('a.page-numbers, .grid-load-more-wrap a.load-more, .grid-infinite-scroll-wrap a.infinite-scroll', $container).off('click').on('click', function (event) {
                // Prevent pagination during search
                var $section_id = $(this).parent().attr('data-section-id') || $(this).closest('[data-section-id]').attr('data-section-id');
                if ($('#' + $section_id).hasClass('search-active')) {
                    event.preventDefault();
                    return false;
                }
                
                if(!GridPlus.vars.grid_on_ajax) {
                    GridPlus.vars.grid_on_ajax = true;
                    var $category_id = $grid_stack_container.attr('data-current-category'),
                        $ajax_url = $grid_stack_container.attr('data-ajax-url'),
                        $nonce = $grid_stack_container.attr('data-nonce'),
                        $href = $(this).attr('href'),
                        $current_page = 1,
                        $filter_type = 2;

                    if ($(this).hasClass('page-numbers')) {
                        $current_page = GridPlus.getPageNumberFromHref($href);
                    } else {
                        if ($(this).hasClass('load-more')) {
                            $current_page = $(this).attr('data-next-page');
                            $filter_type = 3;
                        } else if ($(this).hasClass('infinite-scroll')) {
                            $current_page = $(this).attr('data-next-page');
                            $filter_type = 4;
                        }
                    }

                    GridPlus.filter(this, $section_id, $ajax_url, $category_id, $current_page, $filter_type, $nonce);
                    event.preventDefault();
                    return false;
                }
            });
        },

        initInfiniteScroll: function(){
            // Don't load more items if search is active
            if ($('.search-active').length) {
                return;
            }
            
            var windowTop =  $(window).scrollTop(),
                windowBottom = windowTop + $(window).height(),
                elemTop, elemBottom;
            $('.grid-infinite-scroll-wrap a.infinite-scroll:not(.infinited)').each(function(){
                // Double-check: Don't trigger if parent container has search-active
                var $sectionId = $(this).parent().attr('data-section-id');
                if ($('#' + $sectionId).hasClass('search-active')) {
                    return;
                }
                
                elemTop = $(this).offset().top;
                elemBottom = elemTop + $(this).height();
                if((elemBottom <= windowBottom) && (elemTop >= windowTop)){
                    $(this).addClass('infinited');
                    $(this).trigger('click');
                }
            });
        },

        initAppearScroll: function(){
            var windowTop =  $(window).scrollTop(),
                windowBottom = windowTop + $(window).height(),
                elemTop, elemBottom, $items, $animation = 'fadeInUp';
            $items = [];
            $('.grid-stack-item-content.has-infinite:not(.infinited)').each(function(){
                if(GridPlus.isAppear(this)){
                    $(this).addClass('infinited');
                    $items.push($(this));
                }
            });

            for (var $i = 0; $i < $items.length; $i++) {
                (function ($index) {
                    var $delay = 100 * $i;
                    setTimeout(function () {
                        $animation = $($items[$index]).attr('data-animation');
                        $($items[$index]).addClass('animated ' + $animation);
                        $($items[$index]).removeClass('has-infinite');
                        $($items[$index]).css('opacity', 1);
                    }, $delay);
                })($i);
            }
        },

        initWindowScroll: function(){
            $(window).scroll(function(){
                // Skip infinite scroll if search is active
                if (!$('.search-active').length) {
                    GridPlus.initInfiniteScroll();
                }
                GridPlus.initAppearScroll();
            })
        },

        isAppear: function(elm, percent_height){
            var windowTop =  $(window).scrollTop(),
                windowBottom = windowTop + $(window).height(),
                $height = $(elm).height(),
                elemTop = $(elm).offset().top,
                elemBottom = elemTop + $height,
                $height_appear = elemBottom - windowBottom;
            if(typeof percent_height =='undefined' || isNaN(percent_height)){
                percent_height = 0.8;
            }
            if(( $height_appear <= 0 || $height_appear <= ($height*percent_height) ) && (elemTop >= windowTop  || windowBottom > elemBottom)){
                return true;
            }
            return false;
        },

        initViewGallery: function($container){
            var $grid_plus_container = '';
            if(typeof($container) == 'undefined') {
                $container = $('body');
                $grid_plus_container = $('.grid-plus-container.attachment', $container);
            } else {
                $grid_plus_container = $container;
            }
            if(!$container.hasClass('attachment')) {
                // Use event delegation for better reliability
                $container.off('click.gridplus', 'a.view-gallery').on('click.gridplus', 'a.view-gallery', function(event){
                    // Check if Light Gallery is already open or being initialized
                    if ($('body').hasClass('lg-on') || $(this).data('lg-opening')) {
                        event.preventDefault();
                        event.stopPropagation();
                        return false;
                    }
                    
                    // Mark as opening
                    $(this).data('lg-opening', true);
                    var $clickedElement = $(this);
                    setTimeout(function() {
                        $clickedElement.data('lg-opening', false);
                    }, 2000);
                    
                    event.preventDefault();
                    var $post_item = $(this).closest('.grid-post-item').addClass('active'),
                        $post_id = $(this).attr('data-post-id'),
                        $this = $(this),
                        $ajax_url = $this.attr('data-ajax-url'),
                        ico = $('i',$this).attr('class'),
                        $lg = null;
            
                    
                    // Check if module index was set explicitly (e.g., from URL)
                    // Priority: 1) data-module-index attribute, 2) URL module number, 3) DOM position
                    var urlModuleNumber = null;
                    var urlMatch = window.location.pathname.match(/\/discover\/(\d+)\/?$/);
                    if (urlMatch) {
                        urlModuleNumber = parseInt(urlMatch[1]);
                    }
                    
                    // Get the section ID to look up original positions
                    var $section = $post_item.closest('.grid-plus-container');
                    var sectionId = $section.attr('id');
                    
                    // Check for forced module first
                    var domPosition = $('.grid-post-item').index($post_item) + 1;
                    var dataModuleIndex = $this.attr('data-module-index') ? parseInt($this.attr('data-module-index')) : null;
                    var originalIndex = $post_item.attr('data-original-index') ? parseInt($post_item.attr('data-original-index')) : null;
                    
                    // Try to get original position from stored mapping
                    var storedPosition = null;
                    if (GridPlus.originalPositions[sectionId] && GridPlus.originalPositions[sectionId][$post_id]) {
                        storedPosition = GridPlus.originalPositions[sectionId][$post_id];
                    }
                    
                    var $module_index;
                    
                    if (window.gridPlusForceModule) {
                        $module_index = window.gridPlusForceModule;
                        // Clear it immediately after using to prevent it affecting other clicks
                        window.gridPlusForceModule = null;
                    } else if (dataModuleIndex) {
                        $module_index = dataModuleIndex;
                    } else if (window.gridPlusUrlModule) {
                        $module_index = window.gridPlusUrlModule;
                        // Clear it after use to prevent it affecting subsequent clicks
                        window.gridPlusUrlModule = null;
                    } else if (storedPosition) {
                        $module_index = storedPosition;
                    } else if (originalIndex) {
                        $module_index = originalIndex;
                    } else {
                        $module_index = domPosition;
                    }
                    
                    
                    // Check if URL was already updated by mousedown handler
                    var intendedModule = $this.data('intended-module');
                    if (intendedModule) {
                        $this.removeData('intended-module');
                    } else {
                        // Fallback: Update URL if mousedown didn't fire
                        if (history.pushState && $module_index) {
                            var currentPath = window.location.pathname;
                            var newUrl = '/discover/' + $module_index;
                            if (currentPath !== newUrl && !currentPath.endsWith('/' + $module_index + '/')) {
                                history.pushState({module: $module_index}, '', newUrl);
                            }
                        }
                    }
                    
                    $('i', $this).attr('class', 'fa fa-spinner fa-spin');
                    // Force refresh if shift key is held
                    var forceRefresh = event.shiftKey;
                    
                    if (typeof GridPlus.galleries[$post_id] == 'undefined' || forceRefresh) {
                        // Add timestamp to prevent caching
                        var timestamp = new Date().getTime();
                        $.ajax({
                            url: 'https://arclab.io/wp-json/wp/v2/posts/' + $post_id + '?_embed&_=' + timestamp,
                            type: 'GET',
                            cache: false,
                            success: function (response) {
                                // Handle single post response (not array)
                                var data = response; 
                                $('i', $this).attr('class', ico);
                                var $galleries = [{
                                    src: data.module_fields.moduleImageURL[0],
                                    subHtml: '',
                                    thumb: data.module_fields.moduleImageURL[0],
                                }];
                                // Process tags into HTML
                                var tagsHtml = '';
                                
                                // Try to get tags from embedded data first
                                if (data._embedded && data._embedded['wp:term']) {
                                    var terms = data._embedded['wp:term'];
                                    // wp:term is an array of arrays, tags are usually at index 1
                                    var tags = [];
                                    terms.forEach(function(termGroup) {
                                        termGroup.forEach(function(term) {
                                            if (term.taxonomy === 'post_tag') {
                                                tags.push(term);
                                            }
                                        });
                                    });
                                    
                                    if (tags.length > 0) {
                                        tagsHtml = tags.map(function(tag) {
                                            return '<span class="module-tag">' + tag.name + '</span>';
                                        }).join(' ');
                                    }
                                } 
                                
                                // Always fetch tags using our custom AJAX endpoint to bypass cache
                                if (!tagsHtml || data.tags) {
                                    try {
                                        $.ajax({
                                            url: $ajax_url,
                                            type: 'POST',
                                            async: false,
                                            cache: false,
                                            data: {
                                                action: 'grid_plus_get_post_tags',
                                                post_id: $post_id
                                            },
                                            success: function (response) {
                                                var tagResponse = typeof response === 'string' ? JSON.parse(response) : response;
                                                
                                                if (tagResponse.success && tagResponse.tags && tagResponse.tags.length > 0) {
                                                    tagsHtml = tagResponse.tags.map(function(tag) {
                                                        return '<span class="module-tag">' + tag.name + '</span>';
                                                    }).join(' ');
                                                } else {
                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                console.error('Failed to fetch tags from custom endpoint:', status, error);
                                                // Fallback to REST API tags if available
                                                if (data.tags && data.tags.length > 0) {
                                                    $.ajax({
                                                        url: 'https://arclab.io/wp-json/wp/v2/tags?include=' + data.tags.join(',') + '&per_page=100&_=' + timestamp,
                                                        type: 'GET',
                                                        async: false,
                                                        cache: false,
                                                        success: function (tagData) {
                                                            tagsHtml = tagData.map(function(tag) {
                                                                return '<span class="module-tag">' + tag.name + '</span>';
                                                            }).join(' ');
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    } catch (e) {
                                        console.error('Exception while fetching tags:', e);
                                    }
                                }
                                
                                if (!tagsHtml) {
                                }
                                
                                // Store gallery data in cache
                                GridPlus.galleries[$post_id] = {
                                    galleries: $galleries,
                                    moduleImageURL: data.module_fields.moduleImageURL[0],
                                    moduleLearnerURL: data.module_fields.moduleLearnerURL[0],
                                    moduleEditorURL: data.module_fields.moduleEditorURL[0],
                                    remixCounter: data.module_fields.remixCounter ? data.module_fields.remixCounter[0] : '0',
                                    moduleContent: data.content.rendered,
                                    moduleTitle: data.title.rendered,
                                    moduleTags: tagsHtml,
                                    ajax_url: $ajax_url,
                                    post_id: $post_id
                                };
                                
                                
                                // Destroy any existing instance first
                                var existingInstance = $this.data('lightGallery');
                                if (existingInstance) {
                                    existingInstance.destroy(true);
                                }
                                
                                try {
                                    // Ensure jQuery is properly available for Light Gallery
                                    var $element = $this;
                                    if (!$element.jquery) {
                                        $element = jQuery($this);
                                    }
                                    
                                    $lg = $element.lightGallery({
                                        dynamic: true,
                                        dynamicEl: $galleries,
                                        hash: false, // Disable hash URLs
                                        galleryId: $post_id,
                                        download: true,
                                        fullScreen: false, // Disable fullscreen to avoid the error
                                        enableSwipe: true,
                                        enableDrag: true,
                                        moduleImageURL: data.module_fields.moduleImageURL[0],
                                        moduleLearnerURL: data.module_fields.moduleLearnerURL[0],
                                        moduleEditorURL: data.module_fields.moduleEditorURL[0],
                                        remixCounter: data.module_fields.remixCounter ? data.module_fields.remixCounter[0] : '0',
                                        moduleContent: data.content.rendered,
                                        moduleTitle: data.title.rendered,
                                        moduleTags: tagsHtml,
                                        ajax_url: $ajax_url,
                                        post_id: $post_id,
                                        module_index: $module_index, // Add module index
                                        backdropDuration: 0 // Open immediately when triggered by URL
                                    });
                                    
                                    
                                    GridPlus.light_gallery_after_open($lg);
                                    $post_item.removeClass('active');
                                    
                                    // Store module index globally for fallback
                                    window.gridPlusCurrentModuleIndex = $module_index;
                                    
                                    // Ensure gallery opens immediately
                                    var lgData = $element.data('lightGallery');
                                    if (lgData) {
                                        // Check if gallery needs to be opened (e.g., from URL)
                                        if (!$('body').hasClass('lg-on')) {
                                            setTimeout(function() {
                                                // Trigger the dynamic element click
                                                if (lgData.$items && lgData.$items.length > 0) {
                                                    // Ensure $items is a jQuery object
                                                    var $items = $(lgData.$items);
                                                    $items.eq(0).trigger('click');
                                                } else if (lgData.open) {
                                                    lgData.open();
                                                }
                                            }, 50);
                                        }
                                    }
                                } catch (e) {
                                    console.error('[GridPlus Debug] Error initializing Light Gallery:', e);
                                    console.error('[GridPlus Debug] Error stack:', e.stack);
                                    $post_item.removeClass('active');
                                    
                                    // Try alternative approach
                                    try {
                                        // Use jQuery directly
                                        jQuery($this).lightGallery({
                                            dynamic: true,
                                            dynamicEl: $galleries,
                                            hash: false,
                                            galleryId: $post_id,
                                            fullScreen: false // Disable problematic fullscreen
                                        });
                                    } catch (e2) {
                                        console.error('[GridPlus Debug] Fallback also failed:', e2);
                                    }
                                }
                            },
                            error: function () {
                                $('i', $this).attr('class', ico);
                                $post_item.removeClass('active');
                            }
                        })
                        // $.ajax({
                        //     url: $ajax_url,
                        //     type: 'GET',
                        //     data: ({
                        //         action: 'grid_plus_load_gallery',
                        //         post_id: $post_id
                        //     }),
                        //     // lightbox code is here
                        //     success: function (data) {
                        //         $('i', $this).attr('class', ico);
                        //         var $galleries = JSON.parse(data);
                        //         if(typeof $galleries !='undefined' && $.isArray($galleries) && $galleries.length > 0){
                        //             $lg = $(this).lightGallery({
                        //                 dynamic: true,
                        //                 dynamicEl: $galleries,
                        //                 hash: false,
                        //                 download: true
                        //             });
                        //             GridPlus.light_gallery_after_open($lg);
                        //             GridPlus.galleries[$post_id] = JSON.parse(data);
                        //         }
                        //         $post_item.removeClass('active');
                        //     },
                        //     error: function () {
                        //         $('i', $this).attr('class', ico);
                        //         $post_item.removeClass('active');
                        //     }
                        // });
                    }else{
                        var cachedData = GridPlus.galleries[$post_id];
                        
                        // Destroy any existing instance first
                        var existingInstance = $this.data('lightGallery');
                        if (existingInstance) {
                            existingInstance.destroy(true);
                        }
                        
                        try {
                            // Ensure jQuery is properly available for Light Gallery
                            var $element = $this;
                            if (!$element.jquery) {
                                $element = jQuery($this);
                            }
                            
                            $lg = $element.lightGallery({
                                dynamic: true,
                                dynamicEl: cachedData.galleries,
                                hash: false, // Disable hash URLs
                                galleryId: cachedData.post_id,
                                download: true,
                                fullScreen: false, // Disable fullscreen to avoid the error
                                enableSwipe: true,
                                enableDrag: true,
                                moduleImageURL: cachedData.moduleImageURL,
                                moduleLearnerURL: cachedData.moduleLearnerURL,
                                moduleEditorURL: cachedData.moduleEditorURL,
                                remixCounter: cachedData.remixCounter,
                                moduleContent: cachedData.moduleContent,
                                moduleTitle: cachedData.moduleTitle,
                                moduleTags: cachedData.moduleTags,
                                ajax_url: cachedData.ajax_url,
                                post_id: cachedData.post_id,
                                module_index: $module_index, // Use the already calculated module index
                                backdropDuration: 0 // Open immediately when triggered by URL
                            });
                            GridPlus.light_gallery_after_open($lg);
                            $post_item.removeClass('active');
                            $('i', $this).attr('class', ico);
                            
                            // Store module index globally for fallback
                            window.gridPlusCurrentModuleIndex = $module_index;
                            
                            // Ensure gallery opens immediately for cached data
                            var lgData = $element.data('lightGallery');
                            if (lgData && !$('body').hasClass('lg-on')) {
                                setTimeout(function() {
                                    if (lgData.$items && lgData.$items.length > 0) {
                                        // Ensure $items is a jQuery object
                                        var $items = $(lgData.$items);
                                        $items.eq(0).trigger('click');
                                    } else if (lgData.open) {
                                        lgData.open();
                                    }
                                }, 50);
                            }
                        } catch (e) {
                            console.error('[GridPlus Debug] Error with cached Light Gallery:', e);
                            $post_item.removeClass('active');
                            $('i', $this).attr('class', ico);
                        }
                    }
                });
            }
            if($grid_plus_container.hasClass('attachment')) {
                $grid_plus_container.each(function () {
                    var $id = '#' + $(this).attr('id'),
                        $img = '',
                        $galleries = [];
                    $('a.view-gallery', $id).off('click').on('click', function () {
                        var $this = $(this),
                            ico = $('i', $this).attr('class'),
                            $image_url = $this.attr('data-src'),
                            $index = 0;
                        $('i', $this).attr('class', 'fa fa-spinner fa-spin');
                        $('a.view-gallery', $id).each(function () {
                            $img = $(this).attr('data-src');
                            if (typeof $img != 'undefined' && $img != '') {
                                if ($image_url == $img) {
                                    $index = $galleries.length;
                                }
                                $galleries.push({
                                    subHtml: '',
                                    thumb: $img,
                                    src: $img
                                })
                            }
                        });
                        if ($galleries.length > 0) {
                            var $lg = $this.lightGallery({
                                dynamic: true,
                                dynamicEl: $galleries,
                                hash: false,
                                download: true,
                                index: $index
                            });
                            GridPlus.light_gallery_after_open($lg);
                        }
                        setTimeout(function () {
                            $('i', $this).attr('class', ico);
                        }, 200);
                    })
                });
            }
        },

        light_gallery_after_open: function($lg){
            $lg.on('onAfterOpen.lg', function (event, index) {
                $('.lg-thumb-outer').css('opacity', '0');
                setTimeout(function () {
                    $('.lg-has-thumb').removeClass('lg-thumb-open');
                    $('.lg-thumb-outer').css('opacity', '1');
                }, 700);
            });
        },

        getPageNumberFromHref: function ($href) {
            var $href_default = '',
                pattern = /paged=\d+/ig;
            if (new RegExp(pattern).test($href)) {
                $href_default = new RegExp(pattern).exec($href);
            }else{
                pattern = /page\/\d+/ig;
                $href_default = new RegExp(pattern).test($href) ? new RegExp(pattern).exec($href) : $href_default;
            }
            pattern = /\d+/g;
            return new RegExp(pattern).test($href_default) ? new RegExp(pattern).exec($href_default)[0] : 1;
        },

        initAddToCartAjax: function($container){
            if(typeof($container) == 'undefined') {
                $container = $('body');
            }
            $('a.add-to-cart:not(.added-to-cart)',$container).off('click').on('click',function(event){
                event.preventDefault();
                var $this = $(this),
                    $post_item = $this.closest('.grid-post-item').addClass('active'),
                    $cart_url = $this.attr('data-cart_url'),
                    $cart_redirect_after_add = $this.attr('data-cart_redirect_after_add'),
                    $icon_added_to_cart = $this.attr('data-icon_added_to_cart'),
                    $data = {},
                    $add_to_cart_url = $this.prop('href').replace( '%%endpoint%%', 'add_to_cart' ),
                    $ladda = null;
                if($this.hasClass('ladda-button')){
                    $ladda = Ladda.create(this);
                    $ladda.start();
                }

                $.each( $this.data(), function( key, value ) {
                    $data[key] = value;
                });

                $.ajax({
                    url: $add_to_cart_url,
                    type: 'POST',
                    data: $data,
                    success: function (response) {
                        if($ladda!=null){
                            $ladda.stop();
                        }
                        if ( response.error && response.product_url ) {
                            window.location = response.product_url;
                            return;
                        }
                        if ( $cart_redirect_after_add === 'yes' ) {
                            window.location = $cart_url;
                            return;
                        }
                        if(typeof $icon_added_to_cart != 'undefined' && $icon_added_to_cart != ''){
                            $('i',$this).removeClass();
                            $('i',$this).addClass($icon_added_to_cart);
                        }
                        $this.addClass('added-to-cart');
                        $this.off('click');
                        $this.attr('href',$cart_url);
                        $post_item.removeClass('active');

                        // Trigger event so themes can refresh other areas
                        $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, $this ] );
                    },
                    error: function () {
                        if($ladda!=null){
                            $ladda.stop();
                        }
                        $post_item.removeClass('active');
                    }
                });

            });
        },
        execCategoryFilter: function () {
            $('.grid-category').each(function () {
                if(!$(this).closest('.grid-plus-container').hasClass('grid-cate-multi-line')) {
                    var $this = $(this).removeClass('hidden'),
                        wrapper = $this.closest('.grid-plus-container'),
                        max_width = wrapper.width(),
                        cate_expanded = $this.find('.grid-cate-expanded').removeClass('hidden');
                    var check = false,
                        total_width = 50,
                        item_space = 6;
                    cate_expanded.find('li a').each(function () {
                        var li = $(this).closest('li');
                        $(this).detach().insertBefore(cate_expanded);
                        li.remove();
                    });
                    $this.children('a').each(function () {
                        var target = $(this);
                        if (check) {
                            cate_expanded.find('ul').append('<li>' + target[0].outerHTML + '</li>');
                            target.remove();
                        } else {
                            if ((total_width + target.outerWidth()) > max_width) {
                                check = true;
                                cate_expanded.find('ul').append('<li>' + target[0].outerHTML + '</li>');
                                target.remove();
                            } else {
                                total_width += target.outerWidth() + item_space;
                            }
                        }
                    });
                    if (cate_expanded.find('li a').length > 0) {
                        cate_expanded.removeClass('hidden');
                    } else {
                        cate_expanded.addClass('hidden');
                    }
                    GridPlus.initFilterByCategory();
                }
            });
        },
        
        clearGalleryCache: function() {
            GridPlus.galleries = [];
        },
        
        preloadPostTags: function() {
            // Initialize tags cache if not exists
            if (!GridPlus.postTags) {
                GridPlus.postTags = {};
            }
            
            var postsToLoad = [];
            
            // Find all posts in the grid
            $('.grid-post-item a.view-gallery').each(function() {
                var postId = $(this).attr('data-post-id');
                var ajaxUrl = $(this).attr('data-ajax-url');
                
                if (postId && !GridPlus.postTags[postId]) {
                    postsToLoad.push({id: postId, url: ajaxUrl});
                }
            });
            
            
            // Fetch tags for each post
            postsToLoad.forEach(function(post) {
                $.ajax({
                    url: post.url,
                    type: 'POST',
                    data: {
                        action: 'grid_plus_get_post_tags',
                        post_id: post.id
                    },
                    success: function(response) {
                        var tagResponse = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (tagResponse.success && tagResponse.tags && tagResponse.tags.length > 0) {
                            // Store tags as lowercase string for easy searching
                            var tagNames = tagResponse.tags.map(function(tag) {
                                return tag.name.toLowerCase();
                            }).join(' ');
                            GridPlus.postTags[post.id] = tagNames;
                        } else {
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to load tags for post ' + post.id + ':', error);
                    }
                });
            });
        },
        
        initSearch: function() {
            // Add debounce to prevent too many searches while typing
            var searchTimeout;
            
            // Preload tags for all posts when grid is loaded
            GridPlus.preloadPostTags();
            
            $('.grid-search-input').on('keyup', function() {
                clearTimeout(searchTimeout);
                var $input = $(this);
                
                searchTimeout = setTimeout(function() {
                    var searchText = $input.val().toLowerCase().trim();
                    var sectionId = $input.data('section-id');
                    var $gridContainer = $('#' + sectionId);
                    var $gridItems = $gridContainer.find('.grid-stack-item');
                    var matchedCount = 0;
                    var totalCount = $gridItems.length;
                    
                    if (searchText === '') {
                        // Show all items
                        $gridItems.removeClass('search-hidden').addClass('search-visible');
                        matchedCount = totalCount;
                        
                        // Remove search active class from container
                        $gridContainer.removeClass('search-active');
                        
                        // Reset infinite scroll state
                        $('.grid-infinite-scroll-wrap a.infinite-scroll').removeClass('infinited');
                    } else {
                        // Create array of search terms (split by space for multiple keyword search)
                        var searchTerms = searchText.split(/\s+/).filter(function(term) {
                            return term.length > 0;
                        });
                        
                        var matchedItems = [];
                        var unmatchedItems = [];
                        
                        $gridItems.each(function() {
                            var $item = $(this);
                            var $postItem = $item.find('.grid-post-item');
                            
                            // Get searchable content from various fields
                            // Check for both .grid-title and .title classes since different skins use different classes
                            // Decode HTML entities to handle special characters like & (&amp;)
                            var decodeHtmlEntities = function(text) {
                                var txt = document.createElement("textarea");
                                txt.innerHTML = text;
                                return txt.value;
                            };
                            
                            var title = decodeHtmlEntities($item.find('.grid-title, .title').first().text()).toLowerCase().trim();
                            var content = decodeHtmlEntities($item.find('.grid-excerpt, .excerpt').first().text()).toLowerCase().trim();
                            var category = decodeHtmlEntities($item.find('.grid-category, .category').first().text()).toLowerCase().trim();
                            
                            // Debug: Log what we're finding
                            if (searchTerms.length === 1 && searchTerms[0] === 'debug') {
                            }
                            
                            // Get post ID to check tags from cache
                            var postId = $postItem.find('a.view-gallery').attr('data-post-id');
                            var tags = '';
                            
                            // First check our preloaded tags cache
                            if (postId && GridPlus.postTags && GridPlus.postTags[postId]) {
                                tags = decodeHtmlEntities(GridPlus.postTags[postId]);
                            }
                            // Then check if we have cached gallery data with tags
                            else if (postId && GridPlus.galleries[postId] && GridPlus.galleries[postId].moduleTags) {
                                // Extract text from tag HTML
                                var tempDiv = $('<div>').html(GridPlus.galleries[postId].moduleTags);
                                tags = decodeHtmlEntities(tempDiv.text()).toLowerCase();
                            } else {
                            }
                            
                            // Remove data attribute checking to avoid false matches
                            // Only search through title, content, category, and tags
                            
                            // Priority search: First check title and category for exact matches
                            var titleAndCategory = title + ' ' + category;
                            
                            // Check if search term matches in priority fields
                            var matchFound = false;
                            
                            // For single word searches, do partial matching
                            if (searchTerms.length === 1) {
                                var term = searchTerms[0];
                                // Use partial matching - remove word boundaries for substring search
                                var regex = new RegExp(term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i');
                                
                                // Priority 1: Check title
                                if (regex.test(title)) {
                                    matchFound = true;
                                }
                                // Priority 2: Check category
                                else if (regex.test(category)) {
                                    matchFound = true;
                                }
                                // Priority 3: Check tags
                                else if (regex.test(tags)) {
                                    matchFound = true;
                                }
                            } else {
                                // For multiple words, all must be found in title, category or tags
                                var combinedContent = title + ' ' + category + ' ' + tags;
                                matchFound = searchTerms.every(function(term) {
                                    // Use partial matching for each term
                                    var regex = new RegExp(term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i');
                                    return regex.test(combinedContent);
                                });
                            }
                            
                            var matchesAllTerms = matchFound;
                            
                            if (matchesAllTerms) {
                                $item.removeClass('search-hidden').addClass('search-visible');
                                matchedCount++;
                                
                                // Optional: Highlight matched terms
                                if (searchTerms.length === 1) {
                                    GridPlus.highlightSearchTerm($item, searchTerms[0]);
                                }
                            } else {
                                $item.removeClass('search-visible').addClass('search-hidden');
                                GridPlus.removeHighlight($item);
                            }
                        });
                        
                        // Add search active class to container
                        $gridContainer.addClass('search-active');
                    }
                    
                    // Show search results count
                    var $searchResults = $gridContainer.find('.grid-search-results');
                    if ($searchResults.length === 0) {
                        $searchResults = $('<div class="grid-search-results"></div>');
                        $gridContainer.find('.grid-search-box').after($searchResults);
                    }
                    
                    if (searchText !== '') {
                        var resultsText = 'Found ' + matchedCount + ' of ' + totalCount + ' modules';
                        if (matchedCount === 0) {
                            resultsText = 'No modules found matching "' + searchText + '"';
                        }
                        $searchResults.html(resultsText).show();
                    } else {
                        $searchResults.hide();
                    }
                    
                    // Trigger custom event for other components
                    $gridContainer.trigger('gridSearchComplete', {
                        searchText: searchText,
                        matchedCount: matchedCount,
                        totalCount: totalCount
                    });
                }, 300); // 300ms debounce delay
            });
            
            // Clear search on ESC key
            $('.grid-search-input').on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC key
                    $(this).val('').trigger('keyup');
                }
            });
        },
        
        // Helper function to highlight search terms
        highlightSearchTerm: function($item, term) {
            // Remove existing highlights first
            GridPlus.removeHighlight($item);
            
            // Only highlight in title and excerpt for better UX
            var $elements = $item.find('.grid-title, .grid-excerpt');
            
            $elements.each(function() {
                var $elem = $(this);
                var text = $elem.text();
                var regex = new RegExp('(' + term + ')', 'gi');
                
                if (regex.test(text)) {
                    var newHtml = text.replace(regex, '<span class="search-highlight">$1</span>');
                    $elem.html(newHtml);
                }
            });
        },
        
        // Helper function to remove highlights
        removeHighlight: function($item) {
            $item.find('.search-highlight').each(function() {
                var $this = $(this);
                $this.replaceWith($this.text());
            });
        },
        
        initHashNavigation: function() {
            // Handle clean URL navigation
            
            // Update URL when lightbox opens
            $(document).on('onAfterOpen.lg', function(event) {
                var $lg = $(event.target).data('lightGallery');
                
                // Clear the forced module after Light Gallery opens
                if (window.gridPlusForceModule) {
                    window.gridPlusForceModule = null;
                }
                
                // URL is already updated in the click handler, so we just log here
            });
            
            // Listen for custom Light Gallery navigation
            $(document).on('click', '.lg-prev, .lg-next', function() {
                
                // Get current module from URL
                var currentPath = window.location.pathname;
                var currentModuleMatch = currentPath.match(/\/discover\/(\d+)\/?$/);
                var currentModule = currentModuleMatch ? parseInt(currentModuleMatch[1]) : 1;
                
                // Get total modules
                var totalModules = $('.grid-post-item').length;
                var newModule = currentModule;
                
                if ($(this).hasClass('lg-prev')) {
                    newModule = currentModule > 1 ? currentModule - 1 : totalModules;
                } else {
                    newModule = currentModule < totalModules ? currentModule + 1 : 1;
                }
                
                
                // Update URL
                if (history.pushState && newModule !== currentModule) {
                    var newUrl = '/discover/' + newModule;
                    history.pushState({module: newModule}, '', newUrl);
                    
                    // Close current gallery and open new one
                    var $lg = $('.lg-outer').data('lightGallery');
                    if ($lg) {
                        $lg.destroy();
                        
                        // Open the new module
                        setTimeout(function() {
                            var $targetModule = $('.grid-post-item').eq(newModule - 1);
                            var $link = $targetModule.find('a.view-gallery');
                            if ($link.length) {
                                window.gridPlusForceModule = newModule;
                                $link.trigger('click');
                            }
                        }, 100);
                    }
                }
            });
            
            // Update URL when lightbox closes
            $(document).on('onBeforeClose.lg', function(event) {
                if (history.pushState) {
                    // Return to base /discover/ URL
                    var baseUrl = '/discover/';
                    history.pushState({}, '', baseUrl);
                }
            });
            
            // Handle browser back/forward buttons
            window.addEventListener('popstate', function(event) {
                if (event.state && event.state.module) {
                    // Open the module
                    var $module = $('.grid-post-item:eq(' + (event.state.module - 1) + ')');
                    if ($module.length) {
                        var $link = $module.find('a.view-gallery');
                        if ($link.length) {
                            $link.trigger('click');
                        }
                    }
                } else {
                    // Close any open lightbox
                    var $lg = $('.lg-outer').data('lightGallery');
                    if ($lg) {
                        $lg.destroy();
                    }
                }
            });
        }
    };

    // Early module detection - handle /discover/N URLs directly
    (function() {
        // Check if we're on a /discover/N URL
        var currentPath = window.location.pathname;
        var pathMatch = currentPath.match(/\/discover\/(\d+)\/?$/);
        
        if (pathMatch && pathMatch[1]) {
            var moduleNumber = parseInt(pathMatch[1]);
            
            // Store the module number to open after grid loads
            window.gridPlusModuleToOpen = moduleNumber;
            // Also store as the URL module to force Light Gallery to use it
            window.gridPlusForceModule = moduleNumber;
            
            // Early validation - check if module number seems valid
            if (moduleNumber < 1 || moduleNumber > 100) {
                console.warn('[GridPlus] Module number ' + moduleNumber + ' seems invalid (outside 1-100 range)');
            }
        }
    })();
    
    $(document).ready(function () {
        
        // Track if we came from a direct URL access
        window.gridPlusFromDirectUrl = window.location.pathname.match(/\/discover\/(\d+)\/?$/) !== null;
        
        // Add early URL update handler using event delegation with higher priority
        $(document).on('mousedown.urlupdate', 'a.view-gallery', function(e) {
            
            var $this = $(this);
            var $post_item = $this.closest('.grid-post-item');
            var $section = $post_item.closest('.grid-plus-container');
            var sectionId = $section.attr('id');
            var $post_id = $this.attr('data-post-id');
            
            // Get module index
            var domPosition = $('.grid-post-item').index($post_item) + 1;
            var storedPosition = null;
            if (GridPlus.originalPositions && GridPlus.originalPositions[sectionId] && GridPlus.originalPositions[sectionId][$post_id]) {
                storedPosition = GridPlus.originalPositions[sectionId][$post_id];
            }
            
            var moduleIndex = storedPosition || domPosition;
            
            // Update URL immediately on mousedown (before click)
            if (history.pushState && moduleIndex) {
                var currentPath = window.location.pathname;
                var newUrl = '/discover/' + moduleIndex;
                if (currentPath !== newUrl) {
                    history.pushState({module: moduleIndex}, '', newUrl);
                }
            }
            
            // Store the module index for the click handler
            $this.data('intended-module', moduleIndex);
            
            // Don't prevent default - let events continue
        });
        
        GridPlus.init();
        
        var moduleNumber = null;
        
        // First check if we have a module number from early detection
        if (window.gridPlusModuleToOpen) {
            moduleNumber = window.gridPlusModuleToOpen;
            delete window.gridPlusModuleToOpen; // Clean up
        }
        
        // Check for direct path-based URL like /discover/1
        if (!moduleNumber) {
            var pathMatch = window.location.pathname.match(/\/discover\/(\d+)\/?$/);
            if (pathMatch && pathMatch[1]) {
                moduleNumber = parseInt(pathMatch[1]);
            }
        }
        
        // Check for hash parameter like /discover/#open=1
        if (!moduleNumber) {
            var hashMatch = window.location.hash.match(/#open=(\d+)/);
            if (hashMatch && hashMatch[1]) {
                moduleNumber = parseInt(hashMatch[1]);
                // Clear the hash after reading it
                history.replaceState(null, null, window.location.pathname);
            }
        }
        
        // Fallback to query parameter like /discover/?m=1
        if (!moduleNumber) {
            var urlParams = new URLSearchParams(window.location.search);
            var moduleParam = urlParams.get('m');
            if (moduleParam) {
                moduleNumber = parseInt(moduleParam);
            }
        }
        
        // If we have a module number, open it
        if (moduleNumber && moduleNumber > 0) {
            
            // Function to open the module
            var openModule = function() {
                
                // Check if grid exists
                var $gridContainer = $('.grid-plus-container');
                if (!$gridContainer.length) {
                    return false;
                }
                
                // Check all grid items - try multiple selectors
                var $gridItems = $('.grid-post-item');
                if (!$gridItems.length) {
                    // Try alternative selector
                    $gridItems = $('.grid-stack-item');
                }
                
                var totalModules = $gridItems.length;
                
                // Log first few items for debugging
                if (totalModules > 0) {
                    if (totalModules >= 42) {
                    }
                }
                
                if (totalModules === 0) {
                    return false;
                }
                
                // Don't validate max number if we're checking if module exists
                // Module 42 might exist even if there are exactly 42 modules
                if (moduleNumber < 1) {
                    return true; // Stop trying
                }
                
                // Find the specific module (0-indexed)
                var $targetModule = $gridItems.eq(moduleNumber - 1);
                
                // Check if module exists
                if (!$targetModule.length || moduleNumber > totalModules) {
                    
                    if (attempts === 0) {
                        alert('Module ' + moduleNumber + ' does not exist. This page has ' + totalModules + ' modules.');
                        // DO NOT preserve URL for non-existent modules - redirect to base
                        // Use replace to avoid creating history entry
                        window.location.replace('/discover/');
                    }
                    return true; // Stop trying
                }
                
                
                // Find the link to click
                var $link = $targetModule.find('a.view-gallery');
                if (!$link.length) {
                    // Try alternative selectors
                    $link = $targetModule.find('a[data-post-id]');
                    if (!$link.length) {
                        $link = $targetModule.find('a').first();
                    }
                }
                
                if ($link.length) {
                    
                    // Force update URL to preserve module number
                    if (history.pushState) {
                        history.pushState({module: moduleNumber}, '', '/discover/' + moduleNumber);
                    }
                    
                    // Set the module index on the link for Light Gallery
                    $link.attr('data-module-index', moduleNumber);
                    
                    // Store the intended module globally to ensure Light Gallery uses it
                    window.gridPlusForceModule = moduleNumber;
                    
                    // Trigger click
                    
                    // Check if click handlers are attached
                    var events = $._data($link[0], 'events');
                    if (events && events.click) {
                    } else {
                        GridPlus.initViewGallery($('.grid-plus-container'));
                        // Wait a bit for initialization
                        setTimeout(function() {
                            // Ensure the force module is still set
                            window.gridPlusForceModule = moduleNumber;
                            $link.attr('data-module-index', moduleNumber);
                            $link.trigger('click');
                        }, 500);
                        return true;
                    }
                    
                    $link.trigger('click');
                    
                    // Also try native click as fallback
                    setTimeout(function() {
                        if (!$('.lg-on').length) {
                            if ($('.lg-outer').length) {
                            } else {
                            }
                            
                            if ($link[0] && $link[0].click) {
                                $link[0].click();
                            }
                        } else {
                        }
                    }, 300);
                    
                    return true;
                } else {
                    return false;
                }
            };
            
            // Try to open immediately
            if (!openModule()) {
                var attempts = 0;
                var maxAttempts = 30; // More attempts
                var retryInterval = setInterval(function() {
                    attempts++;
                    
                    if (openModule() || attempts >= maxAttempts) {
                        clearInterval(retryInterval);
                        if (attempts >= maxAttempts) {
                        }
                    }
                }, 300); // Faster retries
            }
        } else {
        }
    });

    var callback_after_resize;
    $(window).resize(function () {
        clearTimeout(callback_after_resize);
        callback_after_resize = setTimeout(function(){
            GridPlus.responsive();
        }, 100);
        setTimeout(function () {
            GridPlus.execCategoryFilter();
        }, 20);
    });
})(jQuery);