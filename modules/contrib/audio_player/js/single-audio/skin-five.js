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
  Drupal.behaviors.audio_player_skin_five = {
    attach: function (context, settings) {
      $(function () {

        once('audio_player_skin_five', '.audio-player.skin-five', context).forEach(function (playerElement) {

        const $player = $(playerElement);

        const $audio = $player.find('.audio-player-audio');
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
        const $progressBar = $player.find('.audio-player-progress-bar');
        const $bufferedBar = $player.find('.audio-player-buffered-bar');
        const $currentTimeSpan = $player.find('.audio-player-current-time');
        const $durationSpan = $player.find('.audio-player-duration');
        const $songNameSpan = $player.find('.audio-player-song-name');

        let isPlaying = false;
        let isSeeking = false; // Flag to prevent timeupdate overriding seek
        let isMuted = false; // Track mute state for toggling
        let previousVolume = audio.volume; // Store previous volume for mute/unmute

        // NEW: Function to update the buffered bar
        function updateBufferedBar() {
          const duration = audio.duration;
          if (!isNaN(duration) && duration > 0) {
            if (audio.buffered.length > 0) {
              const bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
              const bufferedPercent = (bufferedEnd / duration) * 100;
              $bufferedBar.css('width', `${bufferedPercent}%`);
            } else {
              $bufferedBar.css('width', '0%'); // No buffered data yet
            }
          } else {
            $bufferedBar.css('width', '0%'); // Reset if duration is not available
          }
        }

        // Set initial song name (can be dynamic)
        const audioSrc = audio.src;
        const fileName = audioSrc.substring(audioSrc.lastIndexOf('/') + 1);
        $songNameSpan.text(decodeURIComponent(fileName));

        // Play/Pause functionality
        $playPauseBtn.on('click', () => {
          if (isPlaying) {
            audio.pause();
            $playIcon.show();
            $pauseIcon.hide();
          } else {
            audio.play();
            $playIcon.hide();
            $pauseIcon.show();
          }
          isPlaying = !isPlaying;
        });

        // Rewind functionality
        $rewindBtn.on('click', () => {
          audio.currentTime = Math.max(0, audio.currentTime - 10); // Rewind 10 seconds
        });

        // Fast Forward functionality
        $fastForwardBtn.on('click', () => {
          audio.currentTime = Math.min(audio.duration, audio.currentTime + 10); // Fast forward 10 seconds
        });

        // Mute/Unmute functionality
        $muteUnmuteBtn.on('click', () => {
          audio.muted = !audio.muted;
          if (audio.muted) {
            $volumeOnIcon.hide();
            $volumeOffIcon.show();
            $volumeSlider.val(0); // Set slider to 0 when muted
            isMuted = true;
          } else {
            $volumeOnIcon.show();
            $volumeOffIcon.hide();
            // Restore slider to its previous value if it was not 0 before muting
            // This assumes the user didn't manually set volume to 0 before muting
            if (audio.volume === 0 && $volumeSlider.val() === '0') {
              audio.volume = 1; // Default to 1 if it was 0
              $volumeSlider.val(1);
            } else {
              $volumeSlider.val(audio.volume); // Restore slider to actual volume
            }
            isMuted = false;
          }
        });

        // Volume control
        $volumeSlider.on('input', () => {
          audio.volume = $volumeSlider.val();
          if (audio.volume == 0) { // Using == for string/number comparison from slider
            audio.muted = true;
            $volumeOnIcon.hide();
            $volumeOffIcon.show();
            isMuted = true;
          } else {
            audio.muted = false;
            $volumeOnIcon.show();
            $volumeOffIcon.hide();
            isMuted = false;
          }
        });

        // Update progress bar and time
        $audio.on('timeupdate', () => {
          if (!isSeeking && !isNaN(audio.duration) && audio.duration > 0) {
            const progress = (audio.currentTime / audio.duration) * 100;
            $progressBar.css('width', `${progress}%`);
          }
          $currentTimeSpan.text(formatTime(audio.currentTime));
          updateBufferedBar(); // NEW: Update buffered bar on timeupdate as well
        });

        // NEW: Update buffered bar as audio buffers
        $audio.on('progress', updateBufferedBar);

        // Set total duration when metadata is loaded
        $audio.on('loadedmetadata', () => {
          $durationSpan.text(formatTime(audio.duration));
          updateBufferedBar(); // NEW: Initial update for buffered bar on metadata load
        });

        // NEW: Update buffered bar when enough data to start playing is available
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
          $progressBar.css('width', `${percentage * 100}%`); // Update visually immediately
        });

        $(document).on('mouseup', () => {
          if (isSeeking) {
            isSeeking = false;
            if (isPlaying) { // Only resume if it was playing before seek
              audio.play();
            }
            // Ensure play/pause icon is correct after seek
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
          audio.currentTime = 0; // Reset to start
          $progressBar.css('width', '0%');
          $bufferedBar.css('width', '0%'); // NEW: Reset buffered bar
        });

        // Initialize volume slider
        $volumeSlider.val(audio.volume);

        // Initial update for buffered bar if audio is already partially loaded (e.g., from cache)
        updateBufferedBar();
      

        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);