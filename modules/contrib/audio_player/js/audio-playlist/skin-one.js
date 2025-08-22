// audio_player_skin_two_playlist.js

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.audio_player_skin_one_playlist = {
    attach: function (context, settings) {
      // Check for EqualizerEffects availability at the earliest point.
      // If it's not available, log an error and prevent further execution
      // of this specific behavior attachment.
      if (typeof window.EqualizerEffects === 'undefined') {
        console.error("EqualizerEffects library not found. Equalizer functionality will be disabled for this player.");
        // We don't return here immediately for the whole attach,
        // as other parts of the player might still function.
        // Instead, we'll guard specific calls to EqualizerEffects.
      }

      const EqualizerEffects = window.EqualizerEffects; // Will be undefined or the object

      once('audio_player_skin_one_playlist', '.audio-player-container.skin-one-playlist, .audio-player-container.skin-two-playlist', context).forEach(function (playerElement) {
        const $audioPlayerContainer = $(playerElement);

        // Audio element
        const $audioSource = $audioPlayerContainer.find('.audio-player-audio-source');
        const audioSource = $audioSource[0];

        // Playback control buttons
        const $playIconSvg = $audioPlayerContainer.find('.audio-player-play-icon');
        const $pauseIconSvg = $audioPlayerContainer.find('.audio-player-pause-icon');
        const $rewindBtnSvg = $audioPlayerContainer.find('.audio-player-rewind-btn');
        const $forwardBtnSvg = $audioPlayerContainer.find('.audio-player-forward-btn');

        // Progress bar elements
        const $progressBarContainer = $audioPlayerContainer.find('.audio-player-progress-bar-container');
        const $progressBar = $audioPlayerContainer.find('.audio-player-progress-bar');
        const $bufferBar = $audioPlayerContainer.find('.audio-player-buffer-bar');
        const $currentTimeSpan = $audioPlayerContainer.find('.audio-player-current-time');
        const $durationSpan = $audioPlayerContainer.find('.audio-player-duration');

        // Volume controls
        const $volumeIconWrapper = $audioPlayerContainer.find('.audio-player-volume-icon-wrapper');
        const $volumeUpIcon = $audioPlayerContainer.find('.audio-player-volume-up-icon');
        const $volumeMuteIcon = $audioPlayerContainer.find('.audio-player-volume-mute-icon');
        const $customVolumeSlider = $audioPlayerContainer.find('.audio-player-custom-volume-slider');
        const $customVolumeSliderFill = $audioPlayerContainer.find('.audio-player-custom-volume-slider-fill');

        // Mode buttons
        const $randomBtnSvg = $audioPlayerContainer.find('.audio-player-random-btn');
        const $autoplayBtnSvg = $audioPlayerContainer.find('.audio-player-autoplay-btn');
        const playerWrapper = $audioPlayerContainer.find('.audio-player-content-wrapper');

        // Player visibility toggles
        const $caretUp = $audioPlayerContainer.find('.audio-player-caret-up');
        const $caretDown = $audioPlayerContainer.find('.audio-player-caret-down');
        const $playerControls = $audioPlayerContainer.find('.audio-player-controls');
        const $playlistDiv = $audioPlayerContainer.find('.audio-player-playlist');

        // Playlist elements
        const $playlistUl = $audioPlayerContainer.find('.audio-player-playlist-ul');
        const $playlistUlWrapper = $audioPlayerContainer.find('.audio-player-playlist-content');

        // Current track title elements
        const $songMainTitle = $audioPlayerContainer.find('.audio-player-song-main-title');
        const $songSubTitle = $audioPlayerContainer.find('.audio-player-song-sub-title');
        const $songThumbnail = $audioPlayerContainer.find('.audio-player-video-thumbnail');

        const $playerSection = $audioPlayerContainer.find('.audio-player-main-player-section');

        // Equalizer Effect Buttons
        const $equalizerEffectButtons = $audioPlayerContainer.find('.audio-player-eq-effect-btn');

        const musicList = $playlistUl.data('playlist');

        // Equalizer canvas (only if EqualizerEffects is available)
        const $equalizerCanvas = EqualizerEffects ? $audioPlayerContainer.find('.audio-player-equalizer-canvas') : $();
        const equalizerCanvas = $equalizerCanvas[0];
        const canvasCtx = equalizerCanvas ? equalizerCanvas.getContext('2d') : null;

        const element = $audioPlayerContainer.find('.audio-player-progress-bar')[0]; // get the first element directly
        let colorData = {}; // Declare colorData

        if (element) {
          const colorValue = window.getComputedStyle(element).backgroundColor;
          colorData = { backgroundColor: colorValue };
        }

        let currentSongIndex = 0;
        let isPlaying = false;
        let isMuted = false;
        let isRandom = false;
        let isAutoplay = true;
        let lastVolume = 1;

        // Use a data attribute for initial visibility, defaults to true if not set.
        let initialShowEqualizer = $audioPlayerContainer.data('show-equalizer') !== false;

        // Equalizer effect type: default to 'waveform'
        // let equalizerEffectType = 'waveform';
        let equalizerEffectType = $audioPlayerContainer.data('equalizer');
        if (!equalizerEffectType && EqualizerEffects) {
          equalizerEffectType = 'waveform'; // Default if not specified and EQ is present
        }


        // AudioContext for Equalizer
        let audioContext;
        let analyser;
        let bufferLength;
        let timeDomainDataArray;
        let frequencyDataArray;
        let animationFrameId = null; // Will store the ID returned by EqualizerEffects.animate

        // --- Utility Functions ---

        function formatTime(seconds) {
          if (isNaN(seconds) || seconds < 0) return "0:00";
          const minutes = Math.floor(seconds / 60);
          const secs = Math.floor(seconds % 60);
          return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
        }

        function updatePlayPauseIcons() {
          if (isPlaying) {
            $playIconSvg.hide();
            $pauseIconSvg.show();
          } else {
            $playIconSvg.show();
            $pauseIconSvg.hide();
          }
        }

        function updateVolumeIcon() {
          if (isMuted || audioSource.volume === 0) {
            $volumeUpIcon.hide();
            $volumeMuteIcon.show();
          } else {
            $volumeUpIcon.show();
            $volumeMuteIcon.hide();
          }
        }

        function loadSong(index) {
          if (!musicList || index < 0 || index >= musicList.length) {
            console.error("Song index out of bounds or music list is empty.");
            return;
          }

          currentSongIndex = index;
          const song = musicList[currentSongIndex];
          audioSource.src = song.src;
          $songMainTitle.text(song.mainTitle);
          $songSubTitle.text(song.subTitle);

          $playlistUl.find('li').removeClass('audio-player-active-song');
          let $activeItem = $playlistUl.find(`li[data-index="${currentSongIndex}"]`);
          $activeItem.addClass('audio-player-active-song');

          let $activeThumbnail = $activeItem.find('img').first();
          if($activeThumbnail) {
            let $copiedThumbnail = $activeThumbnail.clone();
            $audioPlayerContainer.find('.audio-player-thumbnail-image').html($copiedThumbnail);
          } else {
            $audioPlayerContainer.find('.audio-player-thumbnail-image').html('');
          }

          var $scrollableDiv = $audioPlayerContainer.find('.audio-player-playlist-content');
          var $targetElement = $audioPlayerContainer.find('.audio-player-active-song');

          if ($targetElement.length && $scrollableDiv.length) {
            var scrollPosition = $targetElement.offset().top - $scrollableDiv.offset().top + $scrollableDiv.scrollTop();
            $scrollableDiv.animate({
              scrollTop: scrollPosition
            }, 500);
          }

          if (isPlaying) {
            audioSource.play();
          } else {
            audioSource.load();
            $progressBar.css('width', '0%');
            $bufferBar.css('width', '0%');
            $currentTimeSpan.text('0:00');
            $durationSpan.text('0:00');
          }
        }

        // --- Equalizer Initialization and Drawing Coordination ---
        function setupEqualizer() {
          if (!EqualizerEffects) {
            return; // Don't proceed if EqualizerEffects is not available
          }

          if (!audioContext) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioContext.createAnalyser();
            // Connect the audio source to the analyser
            const sourceNode = audioContext.createMediaElementSource(audioSource);
            sourceNode.connect(analyser);
            analyser.connect(audioContext.destination);

            analyser.fftSize = 2048;
            bufferLength = analyser.frequencyBinCount;
            timeDomainDataArray = new Uint8Array(bufferLength);
            frequencyDataArray = new Uint8Array(bufferLength);

            // Initialize the EqualizerEffects module with the necessary context
            EqualizerEffects.init(equalizerCanvas, canvasCtx, analyser, bufferLength, timeDomainDataArray, frequencyDataArray, animationFrameId, colorData);
          }

          if (audioContext.state === 'suspended') {
            audioContext.resume().then(() => {
            }).catch(e => console.error("Could not resume AudioContext:", e));
          }
        }

        function startEqualizerAnimation() {
          if (!EqualizerEffects || !equalizerCanvas || !canvasCtx || !analyser || !isPlaying || $equalizerCanvas.css('display') === 'none') {
            // If EqualizerEffects is not available, not playing, or canvas not ready/visible,
            // ensure animation is stopped and clear canvas if applicable.
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
            if (equalizerCanvas && canvasCtx) {
                canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);
            }
            return;
          }

          // Ensure canvas size is correct before drawing
          equalizerCanvas.width = equalizerCanvas.offsetWidth;
          equalizerCanvas.height = equalizerCanvas.offsetHeight;

          const selectedEffect = EqualizerEffects.effects[equalizerEffectType] ?? '';
          if (selectedEffect) {
            animationFrameId = EqualizerEffects.animate(selectedEffect);
          } else {
            console.warn(`Equalizer effect '${equalizerEffectType}' not found.`);
            canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
          }
        }
        /**
         * Toggles audio playback state.
         */
        function togglePlayPause() {
          if (isPlaying) {
            audioSource.pause();
            $playerSection.removeClass('playing');
            // Animation cancellation is handled by startEqualizerAnimation based on isPlaying.
          } else {
            // CRITICAL CHANGE: Only call setupEqualizer and attempt to resume
            // when the user explicitly clicks play.
            if (EqualizerEffects && !audioContext) { // Only call if EqualizerEffects is present
              setupEqualizer(); // This creates the AudioContext for the first time
            }

            // Always try to resume the context on a play gesture if it exists.
            // This handles cases where it might have been suspended (e.g., if created on page load,
            // or if the tab was in the background).
            if (audioContext && audioContext.state === 'suspended') {
              audioContext.resume().then(() => {
                audioSource.play();
                $playerSection.addClass('playing');
              }).catch(e => console.error("Could not resume AudioContext:", e));
            } else {
               $playerSection.addClass('playing');
              // If context is already running or not needed, just play.
              audioSource.play();
            }
          }
          isPlaying = !isPlaying;
          updatePlayPauseIcons();
          // Only start equalizer animation if EqualizerEffects is available
          if (EqualizerEffects) {
            startEqualizerAnimation();
          }
        }

        function playNextSong() {
          if (isRandom) {
            let nextIndex;
            do {
              nextIndex = Math.floor(Math.random() * musicList.length);
            } while (nextIndex === currentSongIndex && musicList.length > 1);
            loadSong(nextIndex);
          } else {
            currentSongIndex = (currentSongIndex + 1) % musicList.length;
            loadSong(currentSongIndex);
          }
          audioSource.play();
          isPlaying = true;
          updatePlayPauseIcons();
          if (EqualizerEffects) {
            startEqualizerAnimation();
          }
        }

        function playPreviousSong() {
          if (audioSource.currentTime > 3) {
            audioSource.currentTime = 0;
          } else {
            if (isRandom) {
              let prevIndex;
              do {
                prevIndex = Math.floor(Math.random() * musicList.length);
              } while (prevIndex === currentSongIndex && musicList.length > 1);
              loadSong(prevIndex);
            } else {
              currentSongIndex = (currentSongIndex - 1 + musicList.length) % musicList.length;
              loadSong(currentSongIndex);
            }
          }
          audioSource.play();
          isPlaying = true;
          updatePlayPauseIcons();
          if (EqualizerEffects) {
            startEqualizerAnimation();
          }
        }

        function setVolume(volume) {
          audioSource.volume = volume;
          $customVolumeSliderFill.css('width', `${volume * 100}%`);
          if (volume === 0) {
            isMuted = true;
          } else {
            isMuted = false;
          }
          updateVolumeIcon();
        }

        function toggleMute() {
          if (isMuted) {
            audioSource.volume = lastVolume;
            isMuted = false;
          } else {
            lastVolume = audioSource.volume;
            audioSource.volume = 0;
            isMuted = true;
          }
          updateVolumeIcon();
          $customVolumeSliderFill.css('width', `${audioSource.volume * 100}%`);
        }

        // --- Event Listeners ---

        $playIconSvg.on('click', togglePlayPause);
        $pauseIconSvg.on('click', togglePlayPause);
        $forwardBtnSvg.on('click', playNextSong);
        $rewindBtnSvg.on('click', playPreviousSong);

        // Only attach equalizer effect buttons if EqualizerEffects is available
        if (EqualizerEffects) {
          $equalizerEffectButtons.on('click', function() {
            equalizerEffectType = $(this).data('effect');
            $equalizerEffectButtons.removeClass('audio-player-active-effect');
            $(this).addClass('audio-player-active-effect');

            if (!audioContext) {
                setupEqualizer();
            }

            EqualizerEffects.resetStates(); // Reset any internal state for particle effects etc.

            if (isPlaying) {
              startEqualizerAnimation();
            } else if (equalizerCanvas && canvasCtx) {
               canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);
            }
          });
        }


        $audioSource.on('timeupdate', () => {
          const progress = (audioSource.currentTime / audioSource.duration) * 100;
          $progressBar.css('width', `${progress}%`);
          $currentTimeSpan.text(formatTime(audioSource.currentTime));
        });

        $audioSource.on('loadedmetadata', () => {
          $durationSpan.text(formatTime(audioSource.duration));
          // Only perform equalizer-related canvas sizing and init if EqualizerEffects is available
          if (EqualizerEffects && equalizerCanvas) {
            equalizerCanvas.width = equalizerCanvas.offsetWidth;
            equalizerCanvas.height = equalizerCanvas.offsetHeight;
            // Re-initialize EqualizerEffects with correct canvas size if metadata loads after init
            EqualizerEffects.init(equalizerCanvas, canvasCtx, analyser, bufferLength, timeDomainDataArray, frequencyDataArray, animationFrameId, colorData);
          }
        });

        $audioSource.on('progress', () => {
          if (audioSource.duration > 0) {
            for (let i = 0; i < audioSource.buffered.length; i++) {
              const bufferedEnd = audioSource.buffered.end(audioSource.buffered.length - 1 - i);
              const bufferedStart = audioSource.buffered.start(audioSource.buffered.length - 1 - i);
              if (bufferedEnd > audioSource.currentTime && bufferedStart <= audioSource.currentTime) {
                const bufferWidth = (bufferedEnd / audioSource.duration) * 100;
                $bufferBar.css('width', `${bufferWidth}%`);
                break;
              }
            }
          }
        });

        $audioSource.on('ended', () => {
          if (isAutoplay) {
            playNextSong();
          } else {
            isPlaying = false;
            updatePlayPauseIcons();
            $progressBar.css('width', '0%');
            $currentTimeSpan.text('0:00');
            // Only stop and clear equalizer animation if EqualizerEffects is available
            if (EqualizerEffects) {
              if (animationFrameId) {
                  cancelAnimationFrame(animationFrameId);
                  animationFrameId = null;
              }
              if (equalizerCanvas && canvasCtx) {
                canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);
              }
            }
          }
        });

        // Progress Bar click to seek
        $progressBarContainer.on('click', (e) => {
          const clickX = e.offsetX;
          const width = $progressBarContainer.width();
          const seekTime = (clickX / width) * audioSource.duration;
          audioSource.currentTime = seekTime;
        });

        // Volume Slider functionality
        let isDraggingVolume = false;
        $customVolumeSlider.on('mousedown touchstart', (e) => {
          isDraggingVolume = true;
          const event = e.type === 'touchstart' ? e.originalEvent.touches[0] : e;
          updateVolumeFromEvent(event);
          e.preventDefault();
        });

        $(document).on('mousemove touchmove', (e) => {
          if (isDraggingVolume) {
            const event = e.type === 'touchmove' ? e.originalEvent.touches[0] : e;
            updateVolumeFromEvent(event);
            e.preventDefault();
          }
        });

        $(document).on('mouseup touchend', () => {
          isDraggingVolume = false;
        });

        function updateVolumeFromEvent(e) {
          const rect = $customVolumeSlider[0].getBoundingClientRect();
          let clientX = e.clientX;

          if (clientX < rect.left) {
            clientX = rect.left;
          } else if (clientX > rect.right) {
            clientX = rect.right;
          }

          const clickX = clientX - rect.left;
          const width = rect.width;
          let volume = clickX / width;
          volume = Math.max(0, Math.min(1, volume));
          setVolume(volume);
        }

        // Initial volume setup
        setVolume(audioSource.volume);
        updateVolumeIcon();

        $volumeIconWrapper.on('click', toggleMute);

        // Random button toggle
        $randomBtnSvg.on('click', () => {
          isRandom = !isRandom;
          $randomBtnSvg.toggleClass('audio-player-active', isRandom);
        });

        // Autoplay button toggle
        $autoplayBtnSvg.on('click', () => {
          isAutoplay = !isAutoplay;
          $autoplayBtnSvg.toggleClass('audio-player-active', isAutoplay);
        });

        // Caret toggle for player controls and playlist
        $caretUp.on('click', () => {
          $playerControls.addClass('audio-player-hidden');
          $playlistDiv.addClass('audio-player-hidden');
          $caretUp.hide();
          $caretDown.show();
          // Only show equalizer canvas and start animation if EqualizerEffects is available
          if (EqualizerEffects) {
            $equalizerCanvas.show();
            if (isPlaying) {
                startEqualizerAnimation();
            } else if (equalizerCanvas && canvasCtx) {
                canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);
            }
          } else {
            // If EqualizerEffects is not there, hide the canvas completely.
            $equalizerCanvas.hide();
          }
          playerWrapper.addClass('audio-player-full-width');
        });

        $caretDown.on('click', () => {
          $playerControls.removeClass('audio-player-hidden');
          $playlistDiv.removeClass('audio-player-hidden');
          playerWrapper.removeClass('audio-player-full-width');
          $caretUp.show();
          $caretDown.hide();
          if (EqualizerEffects) { // Only do this if EqualizerEffects is available
            if (!initialShowEqualizer) {
              $equalizerCanvas.hide();
            } else if (isPlaying) {
                startEqualizerAnimation();
            } else {
               if (equalizerCanvas && canvasCtx) {
                   canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);
               }
            }
          } else {
            // Ensure canvas is hidden if EqualizerEffects is not present, regardless of initial setting.
            $equalizerCanvas.hide();
          }
        });


        // Initial setup
        $autoplayBtnSvg.addClass('audio-player-active');
        if (musicList && musicList.length > 0) {
          loadSong(currentSongIndex);
        }

        function updatePlaylistDurations() {
          $playlistUl.find('li').each(function(index) {
            const $listItem = $(this);
            const songSrc = $listItem.data('src'); // Get the song src from data-src attribute

            // Create a temporary audio element to get the duration
            const tempAudio = new Audio(songSrc);

            // Once metadata is loaded, update the duration in the playlist item
            tempAudio.addEventListener('loadedmetadata', () => {
              const duration = formatTime(tempAudio.duration); // Format the duration time
              $listItem.find('span:last-child').text(duration); // Update the duration in the <span> tag
            });
          });

          // Playlist item click (using event delegation for efficiency)
          $playlistUl.on('click', 'li', function () {

            const index = $(this).data('index');
            if (index !== currentSongIndex) {
                $playerSection.addClass('playing');
              loadSong(index);
              isPlaying = true; // Automatically play when a new song is selected from playlist

              // CRITICAL CHANGE: Only call setupEqualizer and attempt to resume
              // when the user explicitly clicks play.
              if (EqualizerEffects && !audioContext) { // Only call if EqualizerEffects is present
                setupEqualizer(); // This creates the AudioContext for the first time
              }

              // Always try to resume the context on a play gesture if it exists.
              // This handles cases where it might have been suspended (e.g., if created on page load,
              // or if the tab was in the background).
              if (audioContext && audioContext.state === 'suspended') {
                audioContext.resume().then(() => {
                  audioSource.play();
                  if (EqualizerEffects) { // Only start animation if EqualizerEffects is present
                    startEqualizerAnimation();
                  }
                }).catch(e => console.error("Could not resume AudioContext:", e));
              } else {
                // If context is already running or not needed, just play.
                audioSource.play();
                if (EqualizerEffects) { // Only start animation if EqualizerEffects is present
                  startEqualizerAnimation();
                }
              }

              updatePlayPauseIcons();
            } else if (!isPlaying) {
              togglePlayPause(); // If clicking the active song and it's paused, play it
            }
          });
        }

        updatePlaylistDurations();

        // Initial equalizer visibility and button active state based on presence of EqualizerEffects
        if (EqualizerEffects) {
          if (!initialShowEqualizer) {
            $equalizerCanvas.hide();
            $caretUp.hide();
            $caretDown.show();
            $playerControls.addClass('audio-player-hidden');
            $playlistDiv.addClass('audio-player-hidden');
          } else {
            $equalizerCanvas.show();
            $caretUp.show();
            $caretDown.hide();
            $playerControls.removeClass('audio-player-hidden');
            $playlistDiv.removeClass('audio-player-hidden');
          }
          $equalizerEffectButtons.filter(`[data-effect="${equalizerEffectType}"]`).addClass('audio-player-active-effect');
        } else {
          // If EqualizerEffects is not present, hide equalizer related elements and buttons.
          $equalizerCanvas.hide();
          $equalizerEffectButtons.hide(); // Hide the equalizer effect buttons
        }


        $(window).on('resize', () => {
          // Only resize and re-init EqualizerEffects if it's available
          if (EqualizerEffects && equalizerCanvas) {
            equalizerCanvas.width = equalizerCanvas.offsetWidth;
            equalizerCanvas.height = equalizerCanvas.offsetHeight;
            // Re-initialize EqualizerEffects with correct canvas size on resize
            EqualizerEffects.init(equalizerCanvas, canvasCtx, analyser, bufferLength, timeDomainDataArray, frequencyDataArray, animationFrameId, colorData);
            if (isPlaying && $equalizerCanvas.css('display') !== 'none') {
              startEqualizerAnimation();
            } else if (canvasCtx) {
                canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);
            }
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);