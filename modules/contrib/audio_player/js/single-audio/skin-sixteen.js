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

  Drupal.behaviors.audio_player_skin_sixteen = {
    attach: function (context, settings) {
      once('audio_player_skin_sixteen', '.audio-player.skin-sixteen', context).forEach(function (playerElement) {

        const $player = $(playerElement);

        // Select elements using jQuery
        const $audio = $player.find('.audio-player-audio');
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
        const $playbackSpeedSelect = $player.find('.audio-player-playback-speed-select');

        // Visualization elements
        const $visualizationContainer = $player.find('.audio-player-visualization-container');
        const $visualizationCanvas = $player.find('.audio-player-visualization-canvas');
        const visualizationCanvas = $visualizationCanvas[0]; // Get native DOM element for canvas context
        const visualizationCtx = visualizationCanvas ? visualizationCanvas.getContext('2d') : null;

        const element = $player.find('.audio-player-progress-bar')[0]; // get the first element directly
        let primaryColor = ''; // Declare primaryColor

        if (element) {
          primaryColor = window.getComputedStyle(element).backgroundColor;
        }

        let audioContext;
        let sourceNode;
        let analyser;
        let frequencyDataArray;
        let bufferLength;

        let isPlaying = false;
        let initialVolume = audio.volume;
        let isSeeking = false;
        let animationFrameId; // For audio visualization

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

        // --- Web Audio API Setup ---
        function setupAudioContext() {
          if (!audioContext && visualizationCanvas) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();

            sourceNode = audioContext.createMediaElementSource(audio);

            analyser = audioContext.createAnalyser();
            analyser.fftSize = 1024; // More data points for a smoother visual
            analyser.minDecibels = -90;
            analyser.maxDecibels = -10;
            analyser.smoothingTimeConstant = 0.8; // Adjust for smoothness vs. responsiveness

            bufferLength = analyser.frequencyBinCount; // This will be 512 for fftSize 1024
            frequencyDataArray = new Uint8Array(bufferLength);

            sourceNode.connect(analyser);
            analyser.connect(audioContext.destination);
          }
        }

        // --- Green Wave and Bar Mix Visualization Function ---
        function drawGreenWaveBarMixViz() {
          if (!visualizationCtx || !analyser || !frequencyDataArray) {
            return; // Exit if context or analyser not set up
          }
          analyser.getByteFrequencyData(frequencyDataArray);

          visualizationCtx.clearRect(0, 0, visualizationCanvas.width, visualizationCanvas.height);

          const barColor = primaryColor; // Solid green bars

          const barWidth = visualizationCanvas.width / bufferLength;
          let x = 0;

          // Draw Bars (first pass)
          for (let i = 0; i < bufferLength; i++) {
            const value = frequencyDataArray[i];
            // Scale height from 0-255 to canvas height
            const barHeight = (value / 255) * visualizationCanvas.height;

            visualizationCtx.fillStyle = barColor;
            // Draw from the bottom up
            visualizationCtx.fillRect(x, visualizationCanvas.height - barHeight, barWidth - 1, barHeight); // -1 for a small gap between bars

            x += barWidth;
          }

          // Draw Wave (second pass, on top of bars)
          visualizationCtx.beginPath();
          visualizationCtx.strokeStyle = primaryColor;
          visualizationCtx.lineWidth = 2; // Thicker line for the wave

          // Start the wave path from the first bar's top-center
          x = 0;
          if (bufferLength > 0) { // Ensure there's data before trying to access
            const firstValue = frequencyDataArray[0];
            const firstBarHeight = (firstValue / 255) * visualizationCanvas.height;
            visualizationCtx.moveTo(x + barWidth / 2, visualizationCanvas.height - firstBarHeight);

            // Connect to the top-center of subsequent bars
            for (let i = 1; i < bufferLength; i++) {
              const value = frequencyDataArray[i];
              const barHeight = (value / 255) * visualizationCanvas.height;
              x += barWidth;
              visualizationCtx.lineTo(x + barWidth / 2, visualizationCanvas.height - barHeight);
            }
          }

          visualizationCtx.stroke();
        }

        // --- Main Animation Loop (for audio viz) ---
        function animateVisualization() {
          animationFrameId = requestAnimationFrame(animateVisualization);

          if (isPlaying) {
            drawGreenWaveBarMixViz(); // Call the new visualization function
          } else {
            if (visualizationCtx) { // Check if context exists before clearing
              // visualizationCtx.clearRect(0, 0, visualizationCanvas.width, visualizationCanvas.height);
            }
          }
        }

        // --- Core Playback Controls ---
        function togglePlayPause() {
          if (!audioContext) {
            setupAudioContext();
            if (visualizationCanvas) { // Check if canvas element exists
              visualizationCanvas.width = visualizationCanvas.offsetWidth;
              visualizationCanvas.height = visualizationCanvas.offsetHeight;
              animateVisualization(); // Start audio visualization loop
            }
          }

          if (audioContext.state === 'suspended') {
            audioContext.resume();
          }

          if (isPlaying) {
            audio.pause();
            $playIcon.show();
            $pauseIcon.hide();
            // $visualizationContainer.removeClass('visible');
          } else {
            audio.play();
            $playIcon.hide();
            $pauseIcon.show();
            $visualizationContainer.addClass('visible');
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

        function toggleMuteUnmute() {
          if (audio.muted) {
            audio.muted = false;
            audio.volume = initialVolume;
            $volumeSlider.val(initialVolume);
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
          $volumeSlider.val(audio.volume);
          initialVolume = audio.volume;
          updateBufferedBar();
        };

        // Trigger when the page loads or when audio metadata is loaded
        $(audio).on('loadedmetadata', updateMetadata);  // When audio metadata is loaded

        updateMetadata();
        
        $audio.on('progress', updateBufferedBar);
        $audio.on('loadeddata', updateBufferedBar);

        $progressContainer.on('mousedown', (e) => {
          isSeeking = true;
          if (isPlaying) {
            audio.pause();
            // $visualizationContainer.removeClass('visible');
          }
          const clickX = e.offsetX;
          const width = $progressContainer.outerWidth();
          const seekTime = (clickX / width) * audio.duration;
          if (!isNaN(seekTime) && isFinite(seekTime)) {
            audio.currentTime = seekTime;
          }
          $progressBar.css('width', `${(audio.currentTime / audio.duration) * 100}%`);
        });

        $(document).on('mouseup', () => {
          if (isSeeking) {
            isSeeking = false;
            if (isPlaying) {
              audio.play();
              $visualizationContainer.addClass('visible');
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

        $playbackSpeedSelect.on('change', (e) => {
          audio.playbackRate = parseFloat(e.target.value);
        });

        $audio.on('ended', () => {
          isPlaying = false;
          $playIcon.show();
          $pauseIcon.hide();
          audio.currentTime = 0;
          $progressBar.css('width', '0%');
          $bufferedBar.css('width', '0%');
          $currentTimeSpan.text('0:00');
          // $visualizationContainer.removeClass('visible');
          if (visualizationCtx) { // Check if context exists before clearing
            visualizationCtx.clearRect(0, 0, visualizationCanvas.width, visualizationCanvas.height);
          }
        });

        // Initial setup for audio
        updateBufferedBar();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);