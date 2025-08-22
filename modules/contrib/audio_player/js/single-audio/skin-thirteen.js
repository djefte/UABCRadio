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

  Drupal.behaviors.audio_player_thirteen = {
    attach: function (context, settings) {
      // Use once to ensure the script runs only once per element.
      once('audio_player_thirteen', '.audio-player.skin-thirteen', context).forEach(function (playerElement) {

        const $player = $(playerElement);

        const $audio = $player.find('.audio-player-my-audio');
        const audio = $audio[0]; // Get the native DOM element for media events and properties

        const $playPauseBtn = $player.find('.audio-player-play-pause-button');
        const $playIcon = $playPauseBtn.find('.audio-player-play-icon');
        const $pauseIcon = $playPauseBtn.find('.audio-player-pause-icon');
        const $muteUnmuteBtn = $player.find('.audio-player-mute-unmute-button');
        const $volumeUpIcon = $muteUnmuteBtn.find('.audio-player-volume-up-icon');
        const $volumeMuteIcon = $muteUnmuteBtn.find('.audio-player-volume-mute-icon');
        const $volumeSlider = $player.find('.audio-player-volume-slider');
        const $progressBar = $player.find('.audio-player-progress-bar');
        const $bufferedBar = $player.find('.audio-player-buffered-bar');
        const $progressContainer = $player.find('.audio-player-progress-container');
        const $currentTimeSpan = $player.find('.audio-player-current-time');
        const $totalTimeSpan = $player.find('.audio-player-total-time');
        const $songNameText = $player.find('.audio-player-song-name');

        let isPlaying = false;
        let initialVolume = audio.volume;

        function togglePlayPause() {
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
        }

        $playPauseBtn.on('click', togglePlayPause);

        function toggleMuteUnmute() {
          if (audio.muted) {
            audio.muted = false;
            audio.volume = initialVolume > 0 ? initialVolume : 1;
            $volumeSlider.val(audio.volume);
            $volumeUpIcon.show();
            $volumeMuteIcon.hide();
          } else {
            initialVolume = audio.volume;
            audio.muted = true;
            audio.volume = 0;
            $volumeSlider.val(0);
            $volumeUpIcon.hide();
            $volumeMuteIcon.show();
          }
        }

        $muteUnmuteBtn.on('click', toggleMuteUnmute);

        $volumeSlider.on('input', (e) => {
          audio.volume = e.target.value;
          if (audio.volume === 0) {
            audio.muted = true;
            $volumeUpIcon.hide();
            $volumeMuteIcon.show();
          } else {
            audio.muted = false;
            $volumeUpIcon.show();
            $volumeMuteIcon.hide();
            initialVolume = audio.volume;
          }
        });

        // formatTime function is already defined outside the behavior and is fine as is.

        $audio.on('timeupdate', () => {
          const duration = audio.duration;
          if (duration > 0) {
            // Update played progress
            const progressPercent = (audio.currentTime / duration) * 100;
            $progressBar.css('width', `${progressPercent}%`);
            $currentTimeSpan.text(formatTime(audio.currentTime));

            // Update buffered progress
            if (audio.buffered.length > 0) {
              const bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
              const bufferedPercent = (bufferedEnd / duration) * 100;
              $bufferedBar.css('width', `${bufferedPercent}%`);
            }
          }
        });

        $audio.on('progress', () => {
          const duration = audio.duration;
          if (duration > 0 && audio.buffered.length > 0) {
            const bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
            const bufferedPercent = (bufferedEnd / duration) * 100;
            $bufferedBar.css('width', `${bufferedPercent}%`);
          }
        });

        // Function to update metadata once loaded
        const updateMetadata = () => {
          $totalTimeSpan.text(formatTime(audio.duration));
          const audioSrc = audio.src;
          const fileName = audioSrc.substring(audioSrc.lastIndexOf('/') + 1);
          $songNameText.text(decodeURIComponent(fileName.replace(/\.[^/.]+$/, "")));
          $volumeSlider.val(audio.volume);
          initialVolume = audio.volume; // Set initialVolume once metadata is loaded
        };

        // Trigger when the page loads or when audio metadata is loaded
        $(audio).on('loadedmetadata', updateMetadata);  // When audio metadata is loaded

        updateMetadata();

        $progressContainer.on('click', (e) => {
          const clickX = e.offsetX;
          const width = $progressContainer.outerWidth();
          const seekTime = (clickX / width) * audio.duration;
          audio.currentTime = seekTime;
        });

        $audio.on('ended', () => {
          isPlaying = false;
          $playIcon.show();
          $pauseIcon.hide();
          audio.currentTime = 0;
          $progressBar.css('width', '0%');
          $bufferedBar.css('width', '0%');
          $currentTimeSpan.text('0:00');
        });

        // Set initial volume slider value when the script attaches
        $volumeSlider.val(audio.volume);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);