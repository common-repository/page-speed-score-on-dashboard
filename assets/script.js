jQuery(document).ready(function($) {
    var pagespeed_ajax_object = {
        ajax_url: pagespeedData.ajax_url,
        // clear_cache_nonce: pagespeedData.clear_cache_nonce,
        scan_images_nonce: pagespeedData.scan_images_nonce,
        image_size_nonce: pagespeedData.image_size_nonce
    };

$('#fetch-pagespeed-scores').click(function() {
        $.ajax({
            url: pagespeedData.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_pagespeed_scores',
                nonce: pagespeedData.fetch_pagespeed_scores_nonce
            },
            success: function(response) {
                if (response.success) {
                    // Handle success
                    var scores = response.data;
                    $('#pagespeed-scores-container').html(
                        '<p>Desktop Score: ' + scores.desktop_score + '</p>' +
                        '<p>Mobile Score: ' + scores.mobile_score + '</p>'
                    );
                } else {
                    // Handle error
                    $('#pagespeed-scores-container').html('<p>Error: ' + response.data.message + '</p>');
                }
            },
            error: function() {
                $('#pagespeed-scores-container').html('<p>Error: Unable to fetch scores.</p>');
            }
        });
    }); 

    function getColor(score) {
        if (score >= 80) {
            return 'green';
        } else if (score >= 40 && score < 80) {
            return 'yellow';
        } else {
            return 'red';
        }
    }

    // $('#clear-cache-button').on('click', function() {
    //     var nonce = pagespeed_ajax_object.clear_cache_nonce; =
    
    //     $.ajax({
    //         url: pagespeed_ajax_object.ajax_url,
    //         type: 'POST',
    //         data: {
    //             action: 'clear_cache',
    //             nonce: nonce
    //         },
    //         success: function(response) {
    //             if (response.success) {
    //                 $('#clear-cache-message').text('Cache cleared successfully.').css('color', 'green');
    //             } else {
    //                 $('#clear-cache-message').text(response.data.message).css('color', 'red');
    //             }
    //         },
    //         error: function() {
    //             $('#clear-cache-message').text('An error occurred while clearing the cache.').css('color', 'red');
    //         }
    //     });
    // });
    
    $('#scan-images-button').on('click', function() {
        $.ajax({
            url: pagespeed_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'scan_images',
                nonce: pagespeed_ajax_object.scan_images_nonce
            },
            success: function(response) {
                if (response.success) {
                    var imagesListContainer = $('#images-list-container');
                    imagesListContainer.html('');
                    var imagesList = '<table>';
                    imagesList += '<thead><tr>';
                    imagesList += '<th class="fixed-width" style="background-color: #f4f4f4;">Sr. No</th>';
                    imagesList += '<th style="width: 300px !important; height: 30px !important; background-color: #f4f4f4;">Image URL</th>';
                    imagesList += '<th style="width: 100px !important; height: 30px !important; background-color: #f4f4f4;">Image Size</th>';
                    imagesList += '</tr></thead>';
                    imagesList += '<tbody>';
            
                    var promises = [];
                    response.data.images.forEach(function(image, index) {
                        var imageSizePromise = fetchImageSize(image);
                        promises.push(imageSizePromise.then(function(size) {
                            imagesList += '<tr>';
                            imagesList += '<td>' + (index + 1) + '</td>';
                            imagesList += '<td style="word-break: break-all;">' + image + '</td>';
                            imagesList += '<td>' + (Math.round(size / 1000 * 100) / 100) + ' kb</td>';
                            imagesList += '</tr>';
                        }).catch(function(error) {
                            console.error('Error fetching image size:', error);
                            imagesList += '<tr>';
                            imagesList += '<td>' + (index + 1) + '</td>';
                            imagesList += '<td>' + image + '</td>';
                            imagesList += '<td>Not Available</td>';
                            imagesList += '</tr>';
                        }));
                    });
            
                    Promise.all(promises).then(function() {
                        imagesList += '</tbody>';
                        imagesList += '</table>';
                        imagesListContainer.html(imagesList);
                    });
                } else {
                    $('#images-list-container').html('<p>Error: ' + response.data.message + '</p>');
                }
            },
            
            error: function(xhr, status, error) {
                console.error('AJAX error: ' + error);
                $('#images-list-container').html('<p>AJAX error: ' + error + '</p>');
            }
        });
    });

    $('#download-pdf-button').on('click', function() {
        if (typeof window.jspdf !== 'undefined') {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.autoTable({
                html: '#images-list-container table'
            });
            doc.save('images_list.pdf');
        } else {
            console.error('jsPDF is not defined.');
        }
    });

    function fetchImageSize(imageUrl) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: pagespeed_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'fetch_image_size',
                    image_url: imageUrl,
                    nonce: pagespeed_ajax_object.image_size_nonce
                },
                success: function(response) {
                    if (response.success) {
                        resolve(response.data.size);
                    } else {
                        reject('Failed to fetch image size');
                    }
                },
                error: function(xhr, status, error) {
                    reject('AJAX error: ' + error);
                }
            });
        });
    }
});
