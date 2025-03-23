(function($) {
    'use strict';

    $(document).ready(function() {
        let currentFilters = {
            author: '',
            price_range: '',
            sort_by: 'newest'
        };

        function loadBooks(page = 1, append = false) {
            const loadMoreBtn = $('.load-more-books');
            loadMoreBtn.prop('disabled', true).text('Loading...');

            $.ajax({
                url: ablAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_more_books',
                    nonce: ablAjax.nonce,
                    page: page,
                    author: currentFilters.author,
                    price_range: currentFilters.price_range,
                    sort_by: currentFilters.sort_by
                },
                success: function(response) {
                    if (response.success) {
                        if (append) {
                            $('.books-grid').append(response.data.html);
                        } else {
                            $('.books-grid').html(response.data.html);
                        }

                        loadMoreBtn.prop('disabled', false).text('Load More');
                        
                        if (!response.data.has_more) {
                            loadMoreBtn.hide();
                        } else {
                            loadMoreBtn.show();
                            loadMoreBtn.data('page', page);
                        }
                    }
                },
                error: function() {
                    loadMoreBtn.prop('disabled', false).text('Load More');
                    alert('Error loading books. Please try again.');
                }
            });
        }

        // Filter change handlers
        $('.author-filter').on('change', function() {
            currentFilters.author = $(this).val();
            loadBooks();
        });

        $('.price-filter').on('change', function() {
            currentFilters.price_range = $(this).val();
            loadBooks();
        });

        $('.sort-filter').on('change', function() {
            currentFilters.sort_by = $(this).val();
            loadBooks();
        });

        // Load more button handler
        $('.load-more-books').on('click', function() {
            const nextPage = parseInt($(this).data('page')) + 1;
            loadBooks(nextPage, true);
        });
    });

})(jQuery);