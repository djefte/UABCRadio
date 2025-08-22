(function ($, Drupal, drupalSettings) {
  'use strict';

  // --- Common Helper Functions (moved outside the loop) ---

  /**
   * Formats time from seconds to MM:SS format.
   * @param {number} seconds - The time in seconds.
   * @returns {string} Formatted time string (MM:SS).
   */
  function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
  }

  // --- Drupal Behavior ---
  Drupal.behaviors.audio_player_skin_six = {
    attach: function (context, settings) {
      // Use once to ensure the script runs only once per element.
      once('audio_player_skin_six', '.audio-player.skin-six', context).forEach(function (playerElement) {

        const $player = $(playerElement);

        const $audio = $player.find('.audio-player-my-audio'); // Corrected selector to match provided code
        const audio = $audio[0]; // Get the native DOM element for media events and properties

        const $playPauseBtn = $player.find('.audio-player-play-pause-btn');
        const $playIcon = $player.find('.audio-player-play-icon');
        const $pauseIcon = $player.find('.audio-player-pause-icon');
        const $rewindBtn = $player.find('.audio-player-rewind-btn');
        const $fastForwardBtn = $player.find('.audio-player-fast-forward-btn');
        const $muteUnmuteBtn = $player.find('.audio-player-mute-unmute-btn');
        const $volumeOnIcon = $player.find('.audio-player-volume-on-icon');
        const $volumeOffIcon = $player.find('.audio-player-volume-off-icon');
        const $volumeSlider = $player.find('.audio-player-volume-slider');
        const $progressBarWrapper = $player.find('.audio-player-progress-bar-wrapper');
        const $songInfo = $player.find('.audio-player-song-info');
        const $progressBar = $player.find('.audio-player-progress-bar');
        const $bufferedBar = $player.find('.audio-player-buffered-bar');
        const $currentTimeSpan = $player.find('.audio-player-current-time');
        const $durationSpan = $player.find('.audio-player-duration');
        const $songNameSpan = $player.find('.audio-player-song-name');

        let isPlaying = false;
        let isSeeking = false; // Flag to prevent timeupdate overriding seek

        $durationSpan.text(formatTime(audio.duration));
        
        // Function to update the buffered bar
        function updateBufferedBar() {
          const duration = audio.duration;
          if (!isNaN(duration) && duration > 0) {
            if (audio.buffered.length > 0) {
              const bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
              const bufferedPercent = (bufferedEnd / duration) * 100;
              $bufferedBar.css('width', `${bufferedPercent}%`);
            } else {
              $bufferedBar.css('width', '0%');
            }
          } else {
            $bufferedBar.css('width', '0%');
          }
        }

        // Set initial song name from the audio source URL
        const audioSrc = audio.src;
        const fileName = audioSrc.substring(audioSrc.lastIndexOf('/') + 1);
        $songNameSpan.text(decodeURIComponent(fileName));

        // Set total duration when metadata is loaded
        $audio.on('loadedmetadata', () => {
          $durationSpan.text(formatTime(audio.duration));
          updateBufferedBar(); // NEW: Initial update for buffered bar on metadata load
        });

        // Play/Pause functionality
        $playPauseBtn.on('click', () => {
          if (isPlaying) {
            audio.pause();
            $playIcon.show();
            $pauseIcon.hide();
            $songInfo.removeClass('audio-player-playing');
          } else {
            audio.play();
            $playIcon.hide();
            $pauseIcon.show();
            $songInfo.addClass('audio-player-playing');
          }
          isPlaying = !isPlaying;
        });

        // Rewind functionality
        $rewindBtn.on('click', () => {
          audio.currentTime = Math.max(0, audio.currentTime - 10);
        });

        // Fast Forward functionality
        $fastForwardBtn.on('click', () => {
          audio.currentTime = Math.min(audio.duration, audio.currentTime + 10);
        });

        // Mute/Unmute functionality
        $muteUnmuteBtn.on('click', () => {
          audio.muted = !audio.muted;
          if (audio.muted) {
            $volumeOnIcon.hide();
            $volumeOffIcon.show();
            $volumeSlider.val(0);
          } else {
            $volumeOnIcon.show();
            $volumeOffIcon.hide();
            if (audio.volume === 0 && $volumeSlider.val() === '0') {
              audio.volume = 1;
              $volumeSlider.val(1);
            } else {
              $volumeSlider.val(audio.volume);
            }
          }
        });

        // Volume control
        $volumeSlider.on('input', () => {
          audio.volume = $volumeSlider.val();
          if (audio.volume == 0) {
            audio.muted = true;
            $volumeOnIcon.hide();
            $volumeOffIcon.show();
          } else {
            audio.muted = false;
            $volumeOnIcon.show();
            $volumeOffIcon.hide();
          }
        });

        // Update progress bar and time
        $audio.on('timeupdate', () => {
          if (!isSeeking && !isNaN(audio.duration) && audio.duration > 0) {
            const progress = (audio.currentTime / audio.duration) * 100;
            $progressBar.css('width', `${progress}%`);
          }
          $currentTimeSpan.text(formatTime(audio.currentTime));
          updateBufferedBar(); 
        });

        // Update buffered bar as audio buffers (primary event for buffering)
        $audio.on('progress', updateBufferedBar);

        // Update buffered bar when enough data to start playing is available
        $audio.on('loadeddata', updateBufferedBar);

        // Handle clicks on progress bar to seek
        $progressBarWrapper.on('mousedown', (e) => {
          isSeeking = true;
          if (isPlaying) {
            audio.pause();
          }
          const rect = $progressBarWrapper[0].getBoundingClientRect();
          const clickX = e.clientX - rect.left;
          const width = rect.width;
          const percentage = clickX / width;
          audio.currentTime = audio.duration * percentage;
          $progressBar.css('width', `${percentage * 100}%`);
        });

        $(document).on('mouseup', () => {
          if (isSeeking) {
            isSeeking = false;
            if (isPlaying) {
              audio.play();
            }
            if (isPlaying) {
              $playIcon.hide();
              $pauseIcon.show();
            } else {
              $playIcon.show();
              $pauseIcon.hide();
            }
          }
        });

        // When song ends
        $audio.on('ended', () => {
          isPlaying = false;
          $playIcon.show();
          $pauseIcon.hide();
          $songInfo.removeClass('audio-player-playing'); // Stop marquee animation
          audio.currentTime = 0;
          $progressBar.css('width', '0%');
          $bufferedBar.css('width', '0%');
          $currentTimeSpan.text('0:00');
        });

        // Initialize volume slider
        $volumeSlider.val(audio.volume);

        // Initial update for buffered bar if audio is already partially loaded (e.g., from cache)
        updateBufferedBar();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);