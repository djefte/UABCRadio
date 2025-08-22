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

  Drupal.behaviors.audio_player = {
    attach: function (context, settings) {
      $(function () {
        once('audio_player', '.audio-player.skin-one', context).forEach(function (playerElement) {
          const $player = $(playerElement);

          // Get references to all necessary DOM elements
          const audioElement = $player.find('.audio-player-element').get(0); // Get native DOM element
          const $playPauseBtn = $player.find('.audio-player-play-pause-button');
          const $playIcon = $player.find('.audio-player-play-icon');
          const $pauseIcon = $player.find('.audio-player-pause-icon');
          const $rewindBtn = $player.find('.audio-player-rewind-button');
          const $forwardBtn = $player.find('.audio-player-forward-button');
          const $volumeIconBtn = $player.find('.audio-player-volume-icon-button');
          const $volumeHighIcon = $player.find('.audio-player-volume-high-icon');
          const $volumeMuteIcon = $player.find('.audio-player-volume-mute-icon');
          const $volumeSlider = $player.find('.audio-player-volume-slider');
          const $progressBarContainer = $player.find('.audio-player-progress-bar-container');
          const $progressBar = $player.find('.audio-player-progress-bar');
          const $bufferedBar = $player.find('.audio-player-buffered-bar');
          const $currentTimeSpan = $player.find('.audio-player-current-time');
          const $durationSpan = $player.find('.audio-player-duration');
          const $songNameDisplay = $player.find('.audio-player-song-name');

          // State variables for playback and mute status
          let isPlaying = false;
          let prevVolume = 1; // Stores volume before muting (when setting slider to 0)
          let isDraggingProgressBar = false; // Flag to track if the progress bar is being dragged
          let isDraggingVolume = false; // Flag to track if the volume slider is being dragged

          $durationSpan.text(formatTime(audioElement.duration));
          
          /**
           * Updates the buffered bar's width based on audio buffering progress.
           */
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

          /**
           * Toggles between play and pause states for the audioElement.
           */
          function togglePlayPause() {
            if (isPlaying) {
              audioElement.pause();
              $playIcon.show();
              $pauseIcon.hide();
              $playPauseBtn.removeClass('audio-player-play-pause-button-active');
            } else {
              audioElement.play();
              $playIcon.hide();
              $pauseIcon.show();
              $playPauseBtn.addClass('audio-player-play-pause-button-active');
            }
            isPlaying = !isPlaying;
          }

          /**
           * Updates audio current time and progress bar based on mouse position.
           * @param {Event} e - The mouse event.
           */
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

          /**
           * Updates the volume of the audio and the volume slider's visual fill.
           * @param {Event} e - The mouse event.
           */
          function updateVolume(e) {
            const rect = $volumeSlider[0].getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const sliderWidth = rect.width;
            let newVolume = (clickX / sliderWidth);

            newVolume = Math.max(0, Math.min(1, newVolume));

            audioElement.volume = newVolume;
            $volumeSlider.val(newVolume);
            $volumeSlider[0].style.setProperty('--value', `${newVolume * 100}%`);
          }

          /**
           * Updates the volume icon based on the current audio volume (muted/unmuted).
           */
          function updateVolumeIcon() {
            if (audioElement.volume === 0 || audioElement.muted) {
              $volumeHighIcon.hide();
              $volumeMuteIcon.show();
            } else {
              $volumeHighIcon.show();
              $volumeMuteIcon.hide();
            }
          }

          // --- Event Listeners ---

          $playPauseBtn.on('click', togglePlayPause);

          $(audioElement).on('timeupdate', () => {
            if (!isDraggingProgressBar && !isNaN(audioElement.duration) && audioElement.duration > 0) {
              const progress = (audioElement.currentTime / audioElement.duration) * 100;
              $progressBar.css('width', `${progress}%`);
            }
            $currentTimeSpan.text(formatTime(audioElement.currentTime));
            updateBufferedBar();
          });

          $(audioElement).on('progress', updateBufferedBar);

          // Function to update metadata once loaded
          const updateMetadata = () => {
            $durationSpan.text(formatTime(audioElement.duration));
            const audioSrc = audioElement.src;
            const fileName = audioSrc.substring(audioSrc.lastIndexOf('/') + 1);
            $songNameDisplay.text(decodeURIComponent(fileName.replace(/\.[^/.]+$/, "")));
            $volumeSlider.val(audioElement.volume);
            updateBufferedBar();
          };

          // Trigger when the page loads or when audio metadata is loaded
          $(audioElement).on('loadedmetadata', updateMetadata);  // When audio metadata is loaded

          updateMetadata();
        
          $(audioElement).on('loadeddata', updateBufferedBar);

          $progressBarContainer.on('mousedown', (e) => {
            isDraggingProgressBar = true;
            if (isPlaying) {
              audioElement.pause();
            }
            updateProgressBar(e);
            e.preventDefault();
          });

          $(document).on('mousemove', (e) => {
            if (isDraggingProgressBar) {
              updateProgressBar(e);
            }
          });

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

          $rewindBtn.on('click', () => {
            audioElement.currentTime = Math.max(0, audioElement.currentTime - 10);
          });

          $forwardBtn.on('click', () => {
            audioElement.currentTime = Math.min(audioElement.duration, audioElement.currentTime + 10);
          });

          $volumeSlider.on('mousedown', (e) => {
            isDraggingVolume = true;
            updateVolume(e);
            e.preventDefault();
          });

          $(document).on('mousemove', (e) => {
            if (isDraggingVolume) {
              updateVolume(e);
            }
          });

          $(document).on('mouseup', () => {
            if (isDraggingVolume) {
              isDraggingVolume = false;
              if (audioElement.volume === 0 && prevVolume === 0) {
                prevVolume = 1;
              } else if (audioElement.volume > 0) {
                prevVolume = audioElement.volume;
              }
            }
          });

          $(audioElement).on('volumechange', () => {
            if (!isDraggingVolume) {
              $volumeSlider.val(audioElement.volume);
              $volumeSlider[0].style.setProperty('--value', `${audioElement.volume * 100}%`);
            }
            updateVolumeIcon();
          });

          $volumeIconBtn.on('click', () => {
            if (audioElement.volume === 0) {
              audioElement.volume = prevVolume;
            } else {
              prevVolume = audioElement.volume;
              audioElement.volume = 0;
            }
            $volumeSlider.val(audioElement.volume);
            $volumeSlider[0].style.setProperty('--value', `${audioElement.volume * 100}%`);
            updateVolumeIcon();
          });

          $(audioElement).on('ended', () => {
            isPlaying = false;
            $playIcon.show();
            $pauseIcon.hide();
            $playPauseBtn.removeClass('audio-player-play-pause-button-active');
            $progressBar.css('width', '0%');
            $bufferedBar.css('width', '0%');
            $currentTimeSpan.text('0:00');
            audioElement.currentTime = 0;
            $volumeSlider[0].style.setProperty('--value', `${audioElement.volume * 100}%`);
            updateVolumeIcon();
          });

          // Set initial song name from the audio source URL
          const audioSrc = audioElement.src;
          const fileName = audioSrc.substring(audioSrc.lastIndexOf('/') + 1);
          $songNameDisplay.text(decodeURIComponent(fileName));

          // Initial calls to set visual states
          updateBufferedBar();
          $volumeSlider[0].style.setProperty('--value', `${audioElement.volume * 100}%`);
          updateVolumeIcon();
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);