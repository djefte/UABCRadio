(function ($, Drupal, drupalSettings) {
  'use strict';

  // --- Common Helper Functions ---

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

  Drupal.behaviors.audio_player_skin_eighteen = {
    attach: function (context, settings) {
      // Use jQuery's .once() for Drupal's attach method.
      // The .once() method ensures the code only runs once per element
      // within the given context.
        
      once('audio_player_skin_eighteen', '.audio-player.skin-eighteen', context).forEach(function (playerElement) {
        const $playerElement = $(playerElement);

        const $audio = $playerElement.find('.audio-player-audio-element'); // Corrected selector to match provided code
        const audio = $audio[0]; // Get the native DOM element for media events and properties

        // const audio = $audio[0]; // Get the native DOM element for audio API
        const $playPauseButton = $playerElement.find('.audio-player-play-pause');
        const $playIcon = $playerElement.find('.audio-player-icon-play');
        const $pauseIcon = $playerElement.find('.audio-player-icon-pause');
        const $progressBar = $playerElement.find('.audio-player-progress-bar');
        const $progress = $playerElement.find('.audio-player-progress');
        const $progressSlider = $playerElement.find('.audio-player-progress-slider');
        const $bufferBar = $playerElement.find('.audio-player-buffer-bar');
        const $currentTimeSpan = $playerElement.find('.audio-player-current-time');
        const $durationSpan = $playerElement.find('.audio-player-duration');
        const $muteUnmuteButton = $playerElement.find('.audio-player-mute-unmute');
        const $volumeOnIcon = $playerElement.find('.audio-player-icon-volume-on');
        const $volumeOffIcon = $playerElement.find('.audio-player-icon-volume-off');
        const $volumeSlider = $playerElement.find('.audio-player-volume-slider');
        const $volumeFill = $playerElement.find('.audio-player-volume-fill');
        const $volumeHandle = $playerElement.find('.audio-player-volume-handle');

        let isDraggingProgress = false;
        let isDraggingVolume = false;

        // --- Helper Functions (adapted to use jQuery where appropriate) ---

        const updatePlayPauseIcon = () => {
          if (audio.paused) {
            $playIcon.show();
            $pauseIcon.hide();
          } else {
            $playIcon.hide();
            $pauseIcon.show();
          }
        };

        const updateVolumeIcon = () => {
          if (audio.muted || audio.volume === 0) {
            $volumeOnIcon.hide();
            $volumeOffIcon.show();
            $muteUnmuteButton.addClass('audio-player-muted-icon');
            $volumeSlider.addClass('audio-player-muted-slider');
          } else {
            $volumeOnIcon.show();
            $volumeOffIcon.hide();
            $muteUnmuteButton.removeClass('audio-player-muted-icon');
            $volumeSlider.removeClass('audio-player-muted-slider');
          }
        };

        // --- Event Listeners (converted to jQuery syntax) ---

        // Play/Pause Toggle
        $playPauseButton.on('click', () => {
          if (audio.paused) {
            audio.play();
          } else {
            audio.pause();
          }
        });

        $audio.on('play', updatePlayPauseIcon);
        $audio.on('pause', updatePlayPauseIcon);

        // Update Progress Bar and Time
        $audio.on('timeupdate', () => {
          if (!isDraggingProgress) { // Only update if not currently dragging
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            $progress.css('width', `${progressPercent}%`);
            $progressSlider.css('left', `${progressPercent}%`);
          }
          $currentTimeSpan.text(formatTime(audio.currentTime));
        });

        // Function to update metadata once loaded
        const updateMetadata = () => {
          $durationSpan.text(formatTime(audio.duration));
          updatePlayPauseIcon(); // Initial icon state
          updateVolumeIcon(); // Initial icon state
        };

        // Trigger when the page loads or when audio metadata is loaded
        $(audio).on('loadedmetadata', updateMetadata);  // When audio metadata is loaded

        // Trigger on page load (if audio is already available)
        updateMetadata();
        
        // Update Buffer Bar
        $audio.on('progress', () => {
          if (audio.buffered.length > 0) {
            const bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
            const bufferPercent = (bufferedEnd / audio.duration) * 100;
            $bufferBar.css('width', `${bufferPercent}%`);
          }
        });

        // Progress Bar Interaction (Seek)
        $progressBar.on('mousedown', (e) => {
          isDraggingProgress = true;
          $progressBar.addClass('dragging');
          updateProgress(e); // Update immediately on click
          $(document).on('mousemove', updateProgress);
          $(document).on('mouseup', stopProgressDrag);
        });

        $progressSlider.on('mousedown', (e) => {
          e.stopPropagation(); // Prevent progressBar's mousedown from firing
          isDraggingProgress = true;
          $progressSlider.addClass('dragging');
          $(document).on('mousemove', updateProgress);
          $(document).on('mouseup', stopProgressDrag);
        });

        const updateProgress = (e) => {
          if (isDraggingProgress) {
            const rect = $progressBar[0].getBoundingClientRect(); // Get native DOM rect
            let clickX = e.clientX - rect.left;
            clickX = Math.max(0, Math.min(clickX, rect.width)); // Clamp within bounds
            const percent = (clickX / rect.width);
            audio.currentTime = percent * audio.duration;
            // Visually update immediately during drag
            $progress.css('width', `${percent * 100}%`);
            $progressSlider.css('left', `${percent * 100}%`);
          }
        };

        const stopProgressDrag = () => {
          isDraggingProgress = false;
          $progressBar.removeClass('dragging');
          $progressSlider.removeClass('dragging');
          $(document).off('mousemove', updateProgress);
          $(document).off('mouseup', stopProgressDrag);
        };

        // Mute/Unmute Toggle
        $muteUnmuteButton.on('click', () => {
          audio.muted = !audio.muted;
          updateVolumeIcon();
          // If unmuted and volume was 0, set to a default (e.g., 0.5)
          if (!audio.muted && audio.volume === 0) {
            audio.volume = 0.5;
            $volumeFill.css('width', '50%');
            $volumeHandle.css('left', '50%');
          } else if (audio.muted) { // If muted, visually set volume to 0
            $volumeFill.css('width', '0%');
            $volumeHandle.css('left', '0%');
          }
        });

        $audio.on('volumechange', () => {
          updateVolumeIcon();
          if (!isDraggingVolume) { // Only update if not currently dragging
            const volumePercent = audio.volume * 100;
            $volumeFill.css('width', `${volumePercent}%`);
            $volumeHandle.css('left', `${volumePercent}%`);
          }
        });

        // Volume Slider Interaction
        $volumeSlider.on('mousedown', (e) => {
          isDraggingVolume = true;
          $volumeSlider.addClass('dragging');
          updateVolume(e); // Update immediately on click
          $(document).on('mousemove', updateVolume);
          $(document).on('mouseup', stopVolumeDrag);
        });

        $volumeHandle.on('mousedown', (e) => {
          e.stopPropagation(); // Prevent volumeSlider's mousedown from firing
          isDraggingVolume = true;
          $volumeHandle.addClass('dragging');
          $(document).on('mousemove', updateVolume);
          $(document).on('mouseup', stopVolumeDrag);
        });

        const updateVolume = (e) => {
          if (isDraggingVolume) {
            const rect = $volumeSlider[0].getBoundingClientRect(); // Get native DOM rect
            let clickX = e.clientX - rect.left;
            clickX = Math.max(0, Math.min(clickX, rect.width)); // Clamp within bounds
            const percent = (clickX / rect.width);
            audio.volume = percent;
            audio.muted = false; // Unmute if dragging volume
            // Visually update immediately during drag
            $volumeFill.css('width', `${percent * 100}%`);
            $volumeHandle.css('left', `${percent * 100}%`);
            updateVolumeIcon(); // Update icon and slider color
          }
        };

        const stopVolumeDrag = () => {
          isDraggingVolume = false;
          $volumeSlider.removeClass('dragging');
          $volumeHandle.removeClass('dragging');
          $(document).off('mousemove', updateVolume);
          $(document).off('mouseup', stopVolumeDrag);
        };

        // Initialize player state
        updatePlayPauseIcon();
        updateVolumeIcon();
        // Set initial volume visually
        $volumeFill.css('width', `${audio.volume * 100}%`);
        $volumeHandle.css('left', `${audio.volume * 100}%`);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);