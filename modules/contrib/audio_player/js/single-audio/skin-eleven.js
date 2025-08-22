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

  Drupal.behaviors.audio_player_skin_eleven = {
    attach: function (context, settings) {
      // Use once to ensure the script runs only once per element.
      once('audio_player_skin_eleven', '.audio-player.skin-eleven', context).forEach(function (playerElement) {

        const $player = $(playerElement);

        const $audio = $player.find('.audio-player-main-audio');
        const audio = $audio[0]; // Get the native DOM element for media events and properties

        const $playPauseBtn = $player.find('.audio-player-play-pause-button');
        const $playIcon = $playPauseBtn.find('.audio-player-play-icon');
        const $pauseIcon = $playPauseBtn.find('.audio-player-pause-icon');
        const $rewindBtn = $player.find('.audio-player-rewind-button');
        const $fastForwardBtn = $player.find('.audio-player-fast-forward-button');
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
        const $artistNameText = $player.find('.audio-player-artist-name');
        const $playbackSpeedSelect = $player.find('.audio-player-speed-select');

        let isPlaying = false;
        let initialVolume = audio.volume;
        let isSeeking = false;

        // --- Helper Functions ---
        // formatTime function is already defined outside the behavior and is fine as is.

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

        // --- Core Playback Controls ---
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

        $rewindBtn.on('click', () => {
          audio.currentTime = Math.max(0, audio.currentTime - 10);
        });

        $fastForwardBtn.on('click', () => {
          audio.currentTime = Math.min(audio.duration, audio.currentTime + 10);
        });

        // --- Mute/Unmute and Volume Control ---
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
          if (audio.volume == 0) {
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

        // --- Progress Bar and Time Display ---
        $audio.on('timeupdate', () => {
          if (!isSeeking && !isNaN(audio.duration) && audio.duration > 0) {
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            $progressBar.css('width', `${progressPercent}%`);
          }
          $currentTimeSpan.text(formatTime(audio.currentTime));
          updateBufferedBar();
        });

        // Function to update metadata once loaded
        const updateMetadata = () => {
          $totalTimeSpan.text(formatTime(audio.duration));
          const audioSrc = audio.src;
          const fileName = audioSrc.substring(audioSrc.lastIndexOf('/') + 1);
          $songNameText.text(decodeURIComponent(fileName.replace(/\.[^/.]+$/, "")));
          $artistNameText.text(""); // This seems to be a hardcoded string, if it should be dynamic, you'd need to get it from a data attribute or drupalSettings.
          $volumeSlider.val(audio.volume);
          initialVolume = audio.volume; // Set initialVolume once metadata is loaded
          updateBufferedBar();
        };

        // Trigger when the page loads or when audio metadata is loaded
        $(audio).on('loadedmetadata', updateMetadata);  // When audio metadata is loaded

        updateMetadata();

        $audio.on('progress', updateBufferedBar);
        $audio.on('loadeddata', updateBufferedBar);

        // Click on progress bar to seek
        $progressContainer.on('mousedown', (e) => {
          isSeeking = true;
          if (isPlaying) {
            audio.pause();
          }
          const clickX = e.offsetX; // jQuery's event object provides offsetX
          const width = $progressContainer.outerWidth();
          const seekTime = (clickX / width) * audio.duration;
          audio.currentTime = seekTime;
          $progressBar.css('width', `${(seekTime / audio.duration) * 100}%`);
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

        // --- Playback Speed Control ---
        $playbackSpeedSelect.on('change', (e) => {
          audio.playbackRate = parseFloat(e.target.value);
        });

        // Handle end of song
        $audio.on('ended', () => {
          isPlaying = false;
          $playIcon.show();
          $pauseIcon.hide();
          audio.currentTime = 0;
          $progressBar.css('width', '0%');
          $bufferedBar.css('width', '0%');
          $currentTimeSpan.text('0:00');
        });

        updateBufferedBar(); // Initial update for buffered bar
      });
    }
  };
})(jQuery, Drupal, drupalSettings);