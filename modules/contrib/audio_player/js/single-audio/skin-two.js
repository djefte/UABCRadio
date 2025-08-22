(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.audio_player_skin_two = {
    attach: function (context, settings) {
      // Use jQuery's .each() with once() to iterate over elements.
      once('audio_player_skin_two', '.audio-player.skin-two', context).forEach(function (playerElement) {
        // Wrap the native DOM element with jQuery for consistent usage.
        const $player = $(playerElement);

        const audio = $player.find('.audio-player-audio')[0];
        const $playPauseBtn = $player.find('.audio-player-play-pause-btn');
        const $rewindBtn = $player.find('.audio-player-rewind-btn');
        const $forwardBtn = $player.find('.audio-player-forward-btn');
        const $muteUnmuteBtn = $player.find('.audio-player-mute-unmute-btn');
        const $volumeSlider = $player.find('.audio-player-volume-slider');
        const $progressBar = $player.find('.audio-player-progress-bar');
        const $bufferedBar = $player.find('.audio-player-buffered-bar');
        const $progressContainer = $player.find('.audio-player-progress-container');
        const $currentTimeSpan = $player.find('.audio-player-current-time');
        const $totalTimeSpan = $player.find('.audio-player-total-time');
        const $songNameSpan = $player.find('.audio-player-song-name');

        let isPlaying = false;
        let currentVolume = audio.volume; // Keep using native audio.volume for direct access

        const $playIconSvg = $playPauseBtn.find('.audio-player-play-icon');
        const $pauseIconSvg = $playPauseBtn.find('.audio-player-pause-icon');
        const $volumeUpIconSvg = $muteUnmuteBtn.find('.audio-player-volume-up-icon');
        const $volumeMuteIconSvg = $muteUnmuteBtn.find('.audio-player-volume-mute-icon');

        function formatTime(seconds) {
          const minutes = Math.floor(seconds / 60);
          const secs = Math.floor(seconds % 60);
          return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
        }

        $playPauseBtn.on('click', () => {
          if (isPlaying) {
            audio.pause();
            $playIconSvg.show();
            $pauseIconSvg.hide();
          } else {
            audio.play();
            $playIconSvg.hide();
            $pauseIconSvg.show();
          }
          isPlaying = !isPlaying;
        });

        $rewindBtn.on('click', () => {
          audio.currentTime = Math.max(0, audio.currentTime - 10);
        });

        $forwardBtn.on('click', () => {
          audio.currentTime = Math.min(audio.duration, audio.currentTime + 10);
        });

        $muteUnmuteBtn.on('click', () => {
          if (audio.muted) {
            audio.muted = false;
            audio.volume = currentVolume;
            $volumeSlider.val(currentVolume);
            $volumeUpIconSvg.show();
            $volumeMuteIconSvg.hide();
          } else {
            currentVolume = audio.volume;
            audio.muted = true;
            audio.volume = 0;
            $volumeSlider.val(0);
            $volumeUpIconSvg.hide();
            $volumeMuteIconSvg.show();
          }
        });

        $volumeSlider.on('input', () => {
          audio.volume = $volumeSlider.val();
          currentVolume = audio.volume;
          if (audio.volume === 0) {
            audio.muted = true;
            $volumeUpIconSvg.hide();
            $volumeMuteIconSvg.show();
          } else {
            audio.muted = false;
            $volumeUpIconSvg.show();
            $volumeMuteIconSvg.hide();
          }
        });

        $(audio).on('timeupdate', () => {
          const duration = audio.duration;
          if (!isNaN(duration)) {
            const progressPercent = (audio.currentTime / duration) * 100;
            $progressBar.css('width', `${progressPercent}%`);
            $currentTimeSpan.text(formatTime(audio.currentTime));

            if (audio.buffered.length > 0) {
              const bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
              const bufferedPercent = (bufferedEnd / duration) * 100;
              $bufferedBar.css('width', `${bufferedPercent}%`);
            }
          }
        });

        $(audio).on('progress', () => {
          const duration = audio.duration;
          if (!isNaN(duration) && audio.buffered.length > 0) {
            const bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
            const bufferedPercent = (bufferedEnd / duration) * 100;
            $bufferedBar.css('width', `${bufferedPercent}%`);
          }
        });

        // Function to update metadata once loaded
        const updateMetadata = () => {
          $totalTimeSpan.text(formatTime(audio.duration));

          const src = $(audio).find('source').attr('src');
          const fileName = src.substring(src.lastIndexOf('/') + 1, src.lastIndexOf('.'));
          $songNameSpan.text(fileName.replace(/%20/g, ' ').replace(/ \(another copy\)/g, ''));

          $volumeSlider.val(audio.volume);
          currentVolume = audio.volume;
        };
  
        // Trigger when the page loads or when audio metadata is loaded
        $(audio).on('loadedmetadata', updateMetadata);  // When audio metadata is loaded

        // Trigger on page load (if audio is already available)
        updateMetadata();

        $progressContainer.on('click', (e) => {
          const clickX = e.offsetX;
          const width = $progressContainer.outerWidth(); // Use outerWidth for consistent behavior
          const duration = audio.duration;
          if (!isNaN(duration) && duration > 0) {
            audio.currentTime = (clickX / width) * duration;
          }
        });

        $(audio).on('ended', () => {
          isPlaying = false;
          $playIconSvg.show();
          $pauseIconSvg.hide();
          audio.currentTime = 0;
          $progressBar.css('width', '0%');
          $bufferedBar.css('width', '0%');
        });

        // Initial state
        if (audio.muted) {
          $volumeUpIconSvg.hide();
          $volumeMuteIconSvg.show();
          $volumeSlider.val(0);
        } else {
          $volumeUpIconSvg.show();
          $volumeMuteIconSvg.hide();
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);