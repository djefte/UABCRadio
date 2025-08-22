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

  Drupal.behaviors.audio_player_fourteen = {
    attach: function (context, settings) {
      // Use once to ensure the script runs only once per element.
      once('audio_player_fourteen', '.audio-player.skin-fourteen', context).forEach(function (playerElement) {

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

        // New: Div Volume Slider Elements
        const $volumeSlider = $player.find('.audio-player-volume-slider'); // The container div
        const $volumeFill = $player.find('.audio-player-volume-fill');     // The fill div

        const $progressBar = $player.find('.audio-player-progress-bar');
        const $bufferedBar = $player.find('.audio-player-buffered-bar');
        const $progressContainer = $player.find('.audio-player-progress-container');
        const $currentTimeSpan = $player.find('.audio-player-current-time');
        const $totalTimeSpan = $player.find('.audio-player-total-time');
        const $songNameText = $player.find('.audio-player-song-name');
        const $playbackSpeedSelect = $player.find('.audio-player-playback-speed');

        // Equalizer elements
        const $equalizerContainer = $player.find('.audio-player-equalizer-container');
        const $eqBars = $player.find('.audio-player-equalizer-bar');

        const element = $player.find('.audio-player-progress-bar')[0]; // get the first element directly
        let primaryColor = ''; // Declare primaryColor

        if (element) {
          primaryColor = window.getComputedStyle(element).backgroundColor;
        }

        // Waveform elements
        const $waveformCanvas = $player.find('.audio-player-waveform-canvas');
        const waveformCanvas = $waveformCanvas[0]; // Get the native DOM element for canvas context
        const canvasCtx = waveformCanvas ? waveformCanvas.getContext('2d') : null; // Check if canvas exists
        let audioContext;
        let analyser;
        let sourceNode;
        let dataArray;
        let bufferLength;

        let isPlaying = false;
        let initialVolume = audio.volume; // Store initial volume for mute/unmute
        let isSeeking = false;
        let isDraggingVolume = false; // New: Flag for volume slider dragging

        // Set initial song name and total time (these will be updated on loadedmetadata)
        $totalTimeSpan.text(formatTime(audio.duration));
        const audioSrc = audio.src;
        const fileName = audioSrc.substring(audioSrc.lastIndexOf('/') + 1);
        $songNameText.text(decodeURIComponent(fileName.replace(/\.[^/.]+$/, "")));
          
        // --- Helper Functions ---

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

        // --- Equalizer Control Functions ---
        function startEqualizerAnimation() {
          $equalizerContainer.removeClass('hidden');
          $eqBars.addClass('playing');
        }

        function stopEqualizerAnimation() {
          $eqBars.removeClass('playing');
          setTimeout(() => {
            $equalizerContainer.addClass('hidden');
          }, 600);
        }

        // --- Waveform Visualization Functions ---
        function setupAudioContext() {
          if (!audioContext && waveformCanvas) { // Ensure canvas exists before setting up context
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioContext.createAnalyser();
            sourceNode = audioContext.createMediaElementSource(audio);

            sourceNode.connect(analyser);
            analyser.connect(audioContext.destination); // Connect analyser to speakers

            analyser.fftSize = 2048; // Controls the number of data points
            bufferLength = analyser.frequencyBinCount; // Half of fftSize
            dataArray = new Uint8Array(bufferLength);
          }
        }

        function drawWaveform() {
          if (!canvasCtx || !analyser || !dataArray) {
            return; // Exit if context or analyser not set up
          }
          requestAnimationFrame(drawWaveform); // Keep drawing
          if (!isPlaying && audio.paused) {
            return; // Don't draw if paused and not playing
          }

          analyser.getByteTimeDomainData(dataArray); // Get waveform data

          canvasCtx.clearRect(0, 0, waveformCanvas.width, waveformCanvas.height); // Clear previous frame

          canvasCtx.lineWidth = 2;
          canvasCtx.strokeStyle = primaryColor; // Waveform color
          canvasCtx.beginPath();

          const sliceWidth = waveformCanvas.width * 1.0 / bufferLength;
          let x = 0;

          for (let i = 0; i < bufferLength; i++) {
            const v = dataArray[i] / 128.0; // Normalize data to 0-2
            const y = v * waveformCanvas.height / 2;

            if (i === 0) {
              canvasCtx.moveTo(x, y);
            } else {
              canvasCtx.lineTo(x, y);
            }
            x += sliceWidth;
          }

          canvasCtx.lineTo(waveformCanvas.width, waveformCanvas.height / 2);
          canvasCtx.stroke();
        }

        // --- Core Playback Controls ---
        function togglePlayPause() {
          // Ensure audio context is setup before playing for the first time
          if (!audioContext) {
            setupAudioContext();
            if (waveformCanvas) {
              // Set initial canvas dimensions to match actual display size
              waveformCanvas.width = waveformCanvas.offsetWidth;
              waveformCanvas.height = waveformCanvas.offsetHeight;
              drawWaveform(); // Start the drawing loop
            }
          }

          if (isPlaying) {
            audio.pause();
            $playIcon.show();
            $pauseIcon.hide();
            stopEqualizerAnimation();
          } else {
            audio.play();
            $playIcon.hide();
            $pauseIcon.show();
            startEqualizerAnimation();
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

        // --- Mute/Unmute and Volume Control (Updated for Div Slider) ---
        // Helper to update the visual state of the volume slider
        function updateVolumeSliderUI(volume) {
            const volumePercent = volume * 100;
            $volumeFill.css('width', `${volumePercent}%`);

            if (volume === 0) {
                $volumeUpIcon.hide();
                $volumeMuteIcon.show();
            } else {
                $volumeUpIcon.show();
                $volumeMuteIcon.hide();
            }
        }

        function toggleMuteUnmute() {
          if (audio.muted) {
            audio.muted = false;
            audio.volume = initialVolume; // Restore to the initialVolume saved before muting
            updateVolumeSliderUI(initialVolume);
          } else {
            initialVolume = audio.volume; // Save current volume before muting
            audio.muted = true;
            audio.volume = 0;
            updateVolumeSliderUI(0);
          }
        }

        $muteUnmuteBtn.on('click', toggleMuteUnmute);

        // New: Volume Slider Div interaction
        $volumeSlider.on('mousedown', function(e) {
            isDraggingVolume = true;
            updateVolumeFromMouseEvent(e);
        });

        $(document).on('mousemove', function(e) {
            if (isDraggingVolume) {
                updateVolumeFromMouseEvent(e);
            }
        });

        $(document).on('mouseup', function() {
            isDraggingVolume = false;
        });

        function updateVolumeFromMouseEvent(e) {
            const sliderRect = $volumeSlider[0].getBoundingClientRect();
            let newVolume = (e.clientX - sliderRect.left) / sliderRect.width;
            newVolume = Math.max(0, Math.min(1, newVolume)); // Clamp between 0 and 1

            audio.volume = newVolume;
            updateVolumeSliderUI(newVolume);
            
            // If dragging and volume becomes 0, ensure it's muted
            if (newVolume === 0) {
                audio.muted = true;
            } else {
                audio.muted = false;
                initialVolume = newVolume; // Update initialVolume for next mute
            }
        }


        // --- Progress Bar and Time Display ---
        $audio.on('timeupdate', () => {
          if (!isSeeking && !isNaN(audio.duration) && audio.duration > 0) {
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            $progressBar.css('width', `${progressPercent}%`);
          }
          $currentTimeSpan.text(formatTime(audio.currentTime));
          updateBufferedBar();
        });

        $audio.on('loadedmetadata', () => {

        });


        // Function to update metadata once loaded
        const updateMetadata = () => {
          $totalTimeSpan.text(formatTime(audio.duration));
          $songNameText.text(decodeURIComponent(fileName.replace(/\.[^/.]+$/, "")));
          updateBufferedBar();

          // Initialize volume slider state on loadedmetadata
          // This ensures the browser's default volume (or previous session's) is reflected
          updateVolumeSliderUI(audio.volume);
          initialVolume = audio.volume; // Set initialVolume from current audio volume
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
            stopEqualizerAnimation();
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
              startEqualizerAnimation();
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
          stopEqualizerAnimation();
        });

        // Initial updates
        updateBufferedBar();
        // The volume slider UI initialization now happens reliably within loadedmetadata.
        // However, we should make sure the initial `initialVolume` is correctly set,
        // especially if `loadedmetadata` is slow or if `audio.volume` has a default.
        initialVolume = audio.volume; // Ensures it's always initialized
        updateVolumeSliderUI(audio.volume); // Ensures visual state matches current audio volume on load


      });
    }
  };
})(jQuery, Drupal, drupalSettings);