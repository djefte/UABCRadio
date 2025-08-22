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

  Drupal.behaviors.audio_player_skin_four = {
    attach: function (context, settings) {
      $(function () {
        once('audio_player_skin_four', '.audio-player.skin-four', context).forEach(function (playerElement) {
          const $player = $(playerElement);

          // Get references to all necessary DOM elements using jQuery
          const $audioElement = $player.find('.audio-player-element');
          const audioElement = $audioElement[0]; // Get the native DOM element for media events and properties

          const $playPauseBtn = $player.find('.audio-player-play-pause-btn');
          const $playIcon = $player.find('.audio-player-play-icon');
          const $pauseIcon = $player.find('.audio-player-pause-icon');
          const $rewindBtn = $player.find('.audio-player-rewind-btn');
          const $forwardBtn = $player.find('.audio-player-fast-forward-btn');
          const $muteUnmuteBtn = $player.find('.audio-player-mute-unmute-btn');
          const $volumeHighIcon = $player.find('.audio-player-volume-high-icon');
          const $volumeMuteIcon = $player.find('.audio-player-volume-mute-icon');
          const $volumeSlider = $player.find('.audio-player-volume-slider');
          const $progressBarContainer = $player.find('.audio-player-progress-container');
          const $progressBar = $player.find('.audio-player-played-progress-bar');
          const $bufferedBar = $player.find('.audio-player-buffered-bar');
          const $currentTimeSpan = $player.find('.audio-player-current-time');
          const $durationSpan = $player.find('.audio-player-duration');
          const $songNameDisplay = $player.find('.audio-player-song-name');

          // State variables for playback and mute status
          let isPlaying = false;
          let isMuted = false;
          let prevVolume = 1; // Stores volume before muting
          let isDraggingProgressBar = false; // Flag to track if the progress bar is being dragged

          $durationSpan.text(formatTime(audioElement.duration));

          // Function to update the buffered bar
          function updateBufferedBar() {
            const duration = audioElement.duration;
            if (!isNaN(duration) && duration > 0) {
              if (audioElement.buffered.length > 0) {
                const bufferedEnd = audioElement.buffered.end(audioElement.buffered.length - 1);
                const bufferedPercent = (bufferedEnd / duration) * 100;
                $bufferedBar.css('width', `${bufferedPercent}%`);
              } else {
                $bufferedBar.css('width', '0%');
              }
            } else {
              $bufferedBar.css('width', '0%');
            }
          }

          // Toggles between play and pause states
          function togglePlayPause() {
            if (isPlaying) {
              audioElement.pause();
              $playIcon.show();
              $pauseIcon.hide();
            } else {
              audioElement.play();
              $playIcon.hide();
              $pauseIcon.show();
            }
            isPlaying = !isPlaying;
          }

          // Event listener for play/pause button click
          $playPauseBtn.on('click', togglePlayPause);

          // Updates the progress bar and current time display as audio plays
          $audioElement.on('timeupdate', () => {
            // Only update if not currently dragging to avoid jerky movement
            if (!isDraggingProgressBar && !isNaN(audioElement.duration) && audioElement.duration > 0) {
              const progress = (audioElement.currentTime / audioElement.duration) * 100;
              $progressBar.css('width', `${progress}%`);
            }
            $currentTimeSpan.text(formatTime(audioElement.currentTime));
            updateBufferedBar(); // Call updateBufferedBar on timeupdate for a more responsive feel
          });

          // Update buffered bar as audio buffers (primary event for buffering)
          $audioElement.on('progress', updateBufferedBar);

          // Sets the total duration once audio metadata is loaded
          $audioElement.on('loadedmetadata', () => {
            $durationSpan.text(formatTime(audioElement.duration));
            updateBufferedBar(); // Initial update for buffered bar on metadata load
          });

          // Ensures buffered bar is updated when enough data to start playing is available
          $audioElement.on('loadeddata', updateBufferedBar);

          // Function to update audio current time based on mouse position
          function updateProgressBar(e) {
            const rect = $progressBarContainer[0].getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const containerWidth = rect.width;

            const newProgress = (clickX / containerWidth) * 100;
            $progressBar.css('width', `${newProgress}%`);

            const newTime = (clickX / containerWidth) * audioElement.duration;
            if (!isNaN(audioElement.duration)) {
              audioElement.currentTime = newTime;
            }
          }

          // Event listener for when the user starts dragging the progress bar
          $progressBarContainer.on('mousedown', (e) => {
            isDraggingProgressBar = true;
            if (isPlaying) {
              audioElement.pause();
            }
            updateProgressBar(e); // Update immediately on mousedown
            e.preventDefault();
          });

          // Event listener for dragging movement (attached to document for broader coverage)
          $(document).on('mousemove', (e) => {
            if (isDraggingProgressBar) {
              updateProgressBar(e);
            }
          });

          // Event listener for when the user releases the mouse button
          $(document).on('mouseup', () => {
            if (isDraggingProgressBar) {
              isDraggingProgressBar = false;
              if (isPlaying) {
                audioElement.play();
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

          // Rewind functionality (skips back 10 seconds)
          $rewindBtn.on('click', () => {
            audioElement.currentTime = Math.max(0, audioElement.currentTime - 10);
          });

          // Forward functionality (skips forward 10 seconds)
          $forwardBtn.on('click', () => {
            audioElement.currentTime = Math.min(audioElement.duration, audioElement.currentTime + 10);
          });

          // Toggles between mute and unmute states
          function toggleMuteUnmute() {
            if (isMuted) {
              audioElement.volume = prevVolume;
              $volumeSlider.val(prevVolume);
              $volumeHighIcon.show();
              $volumeMuteIcon.hide();
            } else {
              prevVolume = audioElement.volume;
              audioElement.volume = 0;
              $volumeSlider.val(0);
              $volumeHighIcon.hide();
              $volumeMuteIcon.show();
            }
            isMuted = !isMuted;
          }

          // Event listener for mute/unmute button click
          $muteUnmuteBtn.on('click', toggleMuteUnmute);

          // Handles volume slider input changes
          $volumeSlider.on('input', (e) => {
            audioElement.volume = e.target.value;
            if (audioElement.volume === 0) {
              isMuted = true;
              $volumeHighIcon.hide();
              $volumeMuteIcon.show();
            } else {
              isMuted = false;
              $volumeHighIcon.show();
              $volumeMuteIcon.hide();
              prevVolume = audioElement.volume;
            }
          });

          // Resets player state when audio finishes
          $audioElement.on('ended', () => {
            isPlaying = false;
            $playIcon.show();
            $pauseIcon.hide();
            $progressBar.css('width', '0%');
            $bufferedBar.css('width', '0%');
            $currentTimeSpan.text('0:00');
            audioElement.currentTime = 0;
          });

          // Set initial song name from the audio source URL
          const audioSrc = audioElement.src;
          const fileName = audioSrc.substring(audioSrc.lastIndexOf('/') + 1);
          $songNameDisplay.text(decodeURIComponent(fileName));

          // Initial call to set buffered bar if audio is already partly loaded (e.g., from cache)
          updateBufferedBar();
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);