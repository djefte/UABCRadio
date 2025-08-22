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

  Drupal.behaviors.audio_player_twelve = {
    attach: function (context, settings) {
      // Use once to ensure the script runs only once per element.
      once('audio_player_twelve', '.audio-player.skin-twelve', context).forEach(function (playerElement) {

        const $player = $(playerElement);

        const $audio = $player.find('.audio-player-audio');
        const audio = $audio[0]; // Get the native DOM element for media events and properties

        const $playPauseBtn = $player.find('.audio-player-play-pause-btn');
        const $playIcon = $playPauseBtn.find('.audio-player-play-icon');
        const $pauseIcon = $playPauseBtn.find('.audio-player-pause-icon');
        const $rewindBtn = $player.find('.audio-player-rewind-btn');
        const $fastForwardBtn = $player.find('.audio-player-fast-forward-btn');
        const $muteUnmuteBtn = $player.find('.audio-player-mute-unmute-btn');
        const $volumeUpIcon = $muteUnmuteBtn.find('.audio-player-volume-up-icon');
        const $volumeMuteIcon = $muteUnmuteBtn.find('.audio-player-volume-mute-icon');
        const $volumeSlider = $player.find('.audio-player-volume-slider');
        const $progressBar = $player.find('.audio-player-progress-bar');
        const $bufferedBar = $player.find('.audio-player-buffered-bar'); // Get buffered bar element
        const $progressContainer = $player.find('.audio-player-progress-container');
        const $currentTimeSpan = $player.find('.audio-player-current-time');
        const $totalTimeSpan = $player.find('.audio-player-total-time');
        const $songNameText = $player.find('.audio-player-song-name');
        const $artistNameText = $player.find('.audio-player-artist-name');
        const $playbackSpeedSelect = $player.find('.audio-player-playback-speed-select');

        let isPlaying = false;
        let initialVolume = audio.volume;
        let isSeeking = false; // Flag for seeking to prevent timeupdate interference

        // --- Helper Functions ---
        // formatTime function is already defined outside the behavior and is fine as is.

        // Function to update the buffered bar
        function updateBufferedBar() {
          const duration = audio.duration;
          if (!isNaN(duration) && duration > 0) {
            if (audio.buffered.length > 0) {
              // Find the last buffered range end
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
            audio.volume = initialVolume > 0 ? initialVolume : 1; // Restore to initial or full if 0
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
          if (audio.volume == 0) { // Using == for string/number comparison from slider
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
          if (!isSeeking && !isNaN(audio.duration) && audio.duration > 0) { // Only update if not seeking
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            $progressBar.css('width', `${progressPercent}%`);
          }
          $currentTimeSpan.text(formatTime(audio.currentTime));
          updateBufferedBar(); // Call buffer update on timeupdate for consistency
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

        // Update buffered bar as audio buffers (crucial event for buffering)
        $audio.on('progress', updateBufferedBar);

        // Update buffered bar when enough data to start playing is available
        $audio.on('loadeddata', updateBufferedBar);

        // Click on progress bar to seek
        $progressContainer.on('mousedown', (e) => {
          isSeeking = true; // Set seeking flag
          if (isPlaying) {
            audio.pause(); // Pause playback during seek
          }
          const clickX = e.offsetX; // X-coordinate of the click relative to the container
          const width = $progressContainer.outerWidth(); // Total width of the progress bar container
          const seekTime = (clickX / width) * audio.duration;
          audio.currentTime = seekTime;
          $progressBar.css('width', `${(seekTime / audio.duration) * 100}%`); // Update visually immediately
        });

        $(document).on('mouseup', () => {
          if (isSeeking) {
            isSeeking = false; // Clear seeking flag
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
          $bufferedBar.css('width', '0%'); // Reset buffered bar
          $currentTimeSpan.text('0:00'); // Reset current time
        });

        // Initial update for buffered bar if audio is already partially loaded (e.g., from cache)
        updateBufferedBar();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);