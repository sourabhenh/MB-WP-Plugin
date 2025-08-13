/**
 * Public JavaScript for the Mastery Box plugin
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        initializeForm();
        initializeGame();
        addInteractiveEffects();
    });

    function initializeForm() {
        $('#mastery-box-form').on('submit', function(e) {
            e.preventDefault();
            handleFormSubmission($(this));
        });
    }

    function handleFormSubmission($form) {
        var $submitBtn = $('#mastery-box-submit');
        var $message = $('#mastery-box-message');
        var originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).html('<span class="loading"></span> Processing...');
        $message.hide().removeClass('success error');

        var formData = new FormData($form[0]);
        formData.append('action', 'mastery_box_submit_form');
        formData.append('nonce', mastery_box_ajax.nonce);

        $.ajax({
            url: mastery_box_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response && response.success) {
                    $message.addClass('success')
                        .text(response.data && response.data.message ? response.data.message : 'Submitted!')
                        .fadeIn();
                    if (response.data && response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1200);
                    } else {
                        setTimeout(function() {
                            $form[0].reset();
                            $message.fadeOut();
                        }, 2500);
                    }
                } else {
                    var err = (response && response.data) ? response.data : 'An error occurred. Please try again.';
                    $message.addClass('error').text(err).fadeIn();
                }
            },
            error: function() {
                $message.addClass('error').text('Network error. Please check your connection and try again.').fadeIn();
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    }

    function initializeGame() {
        $('.mastery-box').on('click', function() {
            var $box = $(this);
            if ($box.hasClass('disabled') || $box.hasClass('flipped')) {
                return;
            }
            $('.mastery-box').addClass('disabled');
            var boxNumber = $box.data('box');
            playGame(boxNumber, $box);
        });

        $(document).on('click', '#play-again-btn', function() {
            if (mastery_box_ajax.form_page_url) {
                window.location.href = mastery_box_ajax.form_page_url;
            } else {
                window.location.reload();
            }
        });
    }

    function playGame(boxNumber, $clickedBox) {
        $clickedBox.find('.box-content').html('<span class="loading"></span>');
        $clickedBox.addClass('flipped');

        var gameData = {
            action: 'mastery_box_play_game',
            nonce: mastery_box_ajax.nonce,
            box: boxNumber
        };

        $.ajax({
            url: mastery_box_ajax.ajax_url,
            type: 'POST',
            data: gameData,
            success: function(response) {
                if (response && response.success && response.data) {
                    handleGameResult(response.data, $clickedBox);
                } else {
                    handleGameError((response && response.data) ? response.data : 'Game error occurred.');
                }
            },
            error: function() {
                handleGameError('Network error. Please try again.');
            }
        });
    }

    function handleGameResult(result, $clickedBox) {
        if (result.is_winner) {
            $clickedBox.find('.box-content').html(
                '<div class="winner-content">🎉 <strong>' +
                escapeHtml(result.gift_name || 'Winner!') + '</strong></div>'
            );
            $clickedBox.addClass('winner-animation');
        } else {
            $clickedBox.find('.box-content').html(
                '<div class="loser-content">😔 <strong>Try Again</strong></div>'
            );
        }
        setTimeout(function() {
            redirectToResultPage();
        }, 1200);
    }

    function redirectToResultPage() {
        var resultPageUrl = '/game-result/';
        if (typeof mastery_box_ajax.result_page_url !== 'undefined' && mastery_box_ajax.result_page_url) {
            resultPageUrl = mastery_box_ajax.result_page_url;
        }
        window.location.href = resultPageUrl;
    }

    function showGameResult(result) {
        var $resultDiv = $('#mastery-box-result');
        var $resultContent = $('#result-content');
        var $playAgainBtn = $('#play-again-btn');
        if (!$resultDiv.length) {
            redirectToResultPage();
            return;
        }
        var resultHtml = '';
        if (result.is_winner) {
            resultHtml = '<div class="result-title">🎉 Congratulations! 🎉</div>';
            resultHtml += '<div class="result-message">' + escapeHtml(result.message || '') + '</div>';
            $resultDiv.addClass('winner').removeClass('loser');
            addConfettiEffect();
        } else {
            resultHtml = '<div class="result-title">😔 Not This Time</div>';
            resultHtml += '<div class="result-message">' + escapeHtml(result.message || '') + '</div>';
            $resultDiv.addClass('loser').removeClass('winner');
        }
        $resultContent.html(resultHtml);
        $resultDiv.fadeIn();
        $playAgainBtn.fadeIn();
    }

    function handleGameError(errorMessage) {
        $('.mastery-box').removeClass('disabled');
        var $resultDiv = $('#mastery-box-result');
        var $resultContent = $('#result-content');
        var $playAgainBtn = $('#play-again-btn');
        if ($resultDiv.length) {
            var errorHtml = '<div class="result-title">⚠️ Error</div>';
            errorHtml += '<div class="result-message">' + escapeHtml(errorMessage) + '</div>';
            $resultContent.html(errorHtml);
            $resultDiv.addClass('loser').removeClass('winner').fadeIn();
            $playAgainBtn.fadeIn();
        } else {
            alert(errorMessage);
        }
    }

    function resetGame() {
        $('.mastery-box').removeClass('disabled flipped winner-animation');
        $('.box-inner').css('transform', '');
        $('.box-content').empty();
        $('#mastery-box-result').fadeOut();
        $('#play-again-btn').hide();
        $('.confetti').remove();
    }

    function addConfettiEffect() {
        var colors = ['#f43f5e', '#06b6d4', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'];
        var confettiCount = 50;
        for (var i = 0; i < confettiCount; i++) {
            (function(iIndex) {
                setTimeout(function() {
                    createConfettiPiece(colors[Math.floor(Math.random() * colors.length)]);
                }, iIndex * 50);
            })(i);
        }
    }

    function createConfettiPiece(color) {
        var $confetti = $('<div class="confetti"></div>');
        $confetti.css({
            position: 'fixed',
            left: Math.random() * 100 + '%',
            top: '-10px',
            width: '10px',
            height: '10px',
            backgroundColor: color,
            zIndex: 9999,
            pointerEvents: 'none'
        });
        $('body').append($confetti);
        $confetti.animate({
            top: $(window).height() + 20,
            left: (Math.random() - 0.5) * 200 + parseInt($confetti.css('left'), 10)
        }, {
            duration: 3000 + Math.random() * 2000,
            easing: 'linear',
            complete: function() {
                $confetti.remove();
            }
        });
        var rotation = 0;
        var rotateInterval = setInterval(function() {
            rotation += 10;
            $confetti.css('transform', 'rotate(' + rotation + 'deg)');
            if (!$confetti.parent().length) {
                clearInterval(rotateInterval);
            }
        }, 50);
    }

    function escapeHtml(text) {
        if (typeof text !== 'string') return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function addInteractiveEffects() {
        $('.mastery-box').on('mouseenter', function() {
            $(this).addClass('hover-effect');
        }).on('mouseleave', function() {
            $(this).removeClass('hover-effect');
        });
        $(document).on('keydown', function(e) {
            if (e.key >= '1' && e.key <= '9') {
                var boxNumber = parseInt(e.key, 10);
                var $box = $('.mastery-box[data-box="' + boxNumber + '"]');
                if ($box.length && !$box.hasClass('disabled') && !$box.hasClass('flipped')) {
                    $box.trigger('click');
                }
            }
        });
    }

})(jQuery);
