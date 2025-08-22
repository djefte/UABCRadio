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

  Drupal.behaviors.audio_player_skin_fifteen = {
    attach: function (context, settings) {
      once('audio_player_skin_fifteen', '.audio-player.skin-fifteen', context).forEach(function (playerElement) {

        const $player = $(playerElement);

        const $audio = $player.find('.audio-player-audio-element');
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

        // Visualization elements (now only one)
        const $visualizationContainer = $player.find('.audio-player-visualization-container');
        const $visualizationCanvas = $player.find('.audio-player-waveform-canvas');
        const visualizationCanvas = $visualizationCanvas[0]; // Get the native DOM element for canvas context
        const visualizationCtx = visualizationCanvas ? visualizationCanvas.getContext('2d') : null;

        const element = $player.find('.audio-player-progress-bar')[0]; // get the first element directly
        let primaryColor = ''; // Declare primaryColor

        if (element) {
          primaryColor = window.getComputedStyle(element).backgroundColor;
        }
        
        let audioContext;
        let sourceNode;
        let analyser; // Single analyser for frequency data
        let frequencyDataArray;
        let bufferLength;

        let isPlaying = false;
        let initialVolume = audio.volume;
        let isSeeking = false;
        let animationFrameId; // To control requestAnimationFrame

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

            // Single Analyser for Frequency Bar Visualization
            analyser = audioContext.createAnalyser();
            analyser.fftSize = 512; // Good balance for bar count and detail
            analyser.minDecibels = -90;
            analyser.maxDecibels = -10;
            analyser.smoothingTimeConstant = 0.85; // Smooths out rapid changes

            bufferLength = analyser.frequencyBinCount; // half of fftSize
            frequencyDataArray = new Uint8Array(bufferLength);

            // Connect nodes: Source -> Analyser -> Destination
            sourceNode.connect(analyser);
            analyser.connect(audioContext.destination);
          }
        }

        // --- NEW: Bar Visualization Function ---
        function drawBars() {
          if (!visualizationCtx || !analyser || !frequencyDataArray) {
            return; // Exit if context or analyser not set up
          }
          analyser.getByteFrequencyData(frequencyDataArray);

          visualizationCtx.clearRect(0, 0, visualizationCanvas.width, visualizationCanvas.height);

          const barWidth = (visualizationCanvas.width / bufferLength) * 1.8; // Adjusted for spacing and fullness
          let x = 0;

          for (let i = 0; i < bufferLength; i++) {
            let barHeight = frequencyDataArray[i] / 255 * visualizationCanvas.height;

            // Optional: Apply a power scale for better visual distribution
            barHeight = Math.pow(barHeight / visualizationCanvas.height, 0.7) * visualizationCanvas.height;

            // Multi-color logic (HSL for dynamic colors)
            // Cycle through colors based on bar index
            const hue = i / bufferLength * 360;
            const saturation = 100;
            // Lighter for taller bars, to make it more vibrant
            const lightness = 40 + (barHeight / visualizationCanvas.height) * 30;

            visualizationCtx.fillStyle = `hsl(${hue}, ${saturation}%, ${lightness}%)`;
            visualizationCtx.fillRect(x, visualizationCanvas.height - barHeight, barWidth, barHeight);

            x += barWidth + 1; // Add 1px gap between bars
          }
        }

        // --- Combined Animation Loop ---
        function visualize() {
          animationFrameId = requestAnimationFrame(visualize);

          if (isPlaying) {
            drawBars();
          } else {
            // When paused, we can clear the canvas or draw a static representation
            // For simplicity, we'll just stop drawing updates.
            // To clear the canvas explicitly when paused:
            // visualizationCtx.clearRect(0, 0, visualizationCanvas.width, visualizationCanvas.height);
          }
        }

        // --- Core Playback Controls ---
        function togglePlayPause() {
          if (!audioContext) {
            setupAudioContext();
            if (visualizationCanvas) {
              // Set initial canvas dimensions to match actual display size
              visualizationCanvas.width = visualizationCanvas.offsetWidth;
              visualizationCanvas.height = visualizationCanvas.offsetHeight;
              visualize(); // Start the visualization loop
            }
          }

          if (audioContext.state === 'suspended') {
            audioContext.resume(); // Resume context if it was suspended by browser policy
          }

          if (isPlaying) {
            audio.pause();
            $playIcon.show();
            $pauseIcon.hide();
            // $visualizationContainer.removeClass('audio-player-visible'); // Hide visualization
          } else {
            audio.play();
            $playIcon.hide();
            $pauseIcon.show();
            $visualizationContainer.addClass('audio-player-visible'); // Show visualization
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
          $volumeSlider.val(audio.volume);
          initialVolume = audio.volume;
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
            // $visualizationContainer.removeClass('audio-player-visible'); // Hide visualization during seek
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
              $visualizationContainer.addClass('audio-player-visible'); // Show visualization on resume
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
          // $visualizationContainer.removeClass('audio-player-visible'); // Hide visualization on song end
          if (visualizationCtx) {
            visualizationCtx.clearRect(0, 0, visualizationCanvas.width, visualizationCanvas.height); // Clear canvas
          }
        });

        // Initial update for buffered bar
        updateBufferedBar();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);