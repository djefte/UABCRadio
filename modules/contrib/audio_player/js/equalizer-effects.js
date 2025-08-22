// equalizer-effects.js

/**
 * @file
 * Contains all equalizer drawing functions.
 * These functions require a pre-initialized AudioContext, AnalyserNode,
 * and a canvas context (canvasCtx) and data arrays (timeDomainDataArray, frequencyDataArray).
 */

(function (exports) {
  // Common variables that each drawing function needs.
  // These will be passed in from the main script.
  let equalizerCanvas = null;
  let canvasCtx = null;
  let analyser = null;
  let bufferLength = 0;
  let timeDomainDataArray = null;
  let frequencyDataArray = null;
  let animationFrameId = null;
  let equalizerColorData = null;

  // Variables for specific effects that need persistent state
  let rotationAngle = 0; // For Rotating Ring and Vortex Spectrum
  let particles = [];    // For Raindrop Effect
  let fireflies = [];    // For Fireflies / Swarm
  let tunnelOffset = 0;  // For Tunnel Effect
  let vortexAngle = 0;   // For Vortex Spectrum
  let lightningParticles = []; // For Rain and Lightning

  /**
   * Initializes the common variables required by all drawing functions.
   * This should be called once when the equalizer is set up.
   */
  function initEqualizerEffects(canvas, ctx, an, bufLen, timeData, freqData, animId, colorData) {
    equalizerCanvas = canvas;
    canvasCtx = ctx;
    analyser = an;
    bufferLength = bufLen;
    timeDomainDataArray = timeData;
    frequencyDataArray = freqData;
    animationFrameId = animId; // We'll update this by returning the new ID
    equalizerColorData = colorData; // We'll update this by returning the new ID
  }

  /**
   * Helper to request animation frame and recursively call the draw function.
   * @param {Function} drawFn The actual drawing function (e.g., drawWaveform)
   */
  function animate(drawFn) {
    if (!equalizerCanvas || !canvasCtx || !analyser) return null; // Ensure essentials exist

    // If there's an ongoing animation, cancel it before starting a new one.
    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId);
    }

    animationFrameId = requestAnimationFrame(() => animate(drawFn)); // Update global ID
    drawFn(); // Execute the actual drawing logic for the chosen effect
    return animationFrameId; // Return the new ID to the main script
  }

  // --- Drawing Functions (same as before, but now within this module) ---

  // 1. Waveform (Line)
  function drawWaveform() {
    analyser.getByteTimeDomainData(timeDomainDataArray);

    // var colorValue = getComputedStyle(document.documentElement).getPropertyValue('--playlist-accent-color').trim();
    var colorValue = equalizerColorData.backgroundColor;

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);
    canvasCtx.lineWidth = 2;
    canvasCtx.strokeStyle = colorValue; // Blue
    canvasCtx.beginPath();

    const sliceWidth = equalizerCanvas.width * 1.0 / bufferLength;
    let x = 0;

    for (let i = 0; i < bufferLength; i++) {
      const v = timeDomainDataArray[i] / 128.0; // Normalize 0-255 to 0-2
      const y = v * equalizerCanvas.height / 2; // Scale to canvas height

      if (i === 0) {
        canvasCtx.moveTo(x, y);
      } else {
        canvasCtx.lineTo(x, y);
      }
      x += sliceWidth;
    }
    canvasCtx.lineTo(equalizerCanvas.width, equalizerCanvas.height / 2);
    canvasCtx.stroke();
  }

  // 2. Frequency Bars
  function drawFrequencyBars() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const barWidth = (equalizerCanvas.width / bufferLength) * 2.5;
    let x = 0;

    for (let i = 0; i < bufferLength; i++) {
      let barHeight = frequencyDataArray[i] / 255 * equalizerCanvas.height;

      const gradient = canvasCtx.createLinearGradient(0, equalizerCanvas.height, 0, 0);
      gradient.addColorStop(0, 'rgba(26, 115, 232, 0.2)');
      gradient.addColorStop(0.5, 'rgba(26, 115, 232, 0.7)');
      gradient.addColorStop(1, '#1a73e8');
      canvasCtx.fillStyle = gradient;

      canvasCtx.fillRect(x, equalizerCanvas.height - barHeight, barWidth, barHeight);
      x += barWidth + 1;
    }
  }

  // 3. Circular Waveform
  function drawCircularWaveform() {
    analyser.getByteTimeDomainData(timeDomainDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const radius = Math.min(centerX, centerY) * 0.4; // Base radius

    canvasCtx.lineWidth = 2;
    canvasCtx.strokeStyle = '#1a73e8';
    canvasCtx.beginPath();

    for (let i = 0; i < bufferLength; i++) {
      const v = timeDomainDataArray[i] / 128.0; // Normalized value 0-2
      const r = radius + (v - 1) * radius * 0.8; // Modulate radius based on amplitude (-1 to 1 range)

      const angle = (i / bufferLength) * Math.PI * 2; // Full circle
      const x = centerX + r * Math.cos(angle);
      const y = centerY + r * Math.sin(angle);

      if (i === 0) {
        canvasCtx.moveTo(x, y);
      } else {
        canvasCtx.lineTo(x, y);
      }
    }
    canvasCtx.closePath();
    canvasCtx.stroke();
  }

  // 4. Dot Waveform
  function drawDotWaveform() {
    analyser.getByteTimeDomainData(timeDomainDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const sliceWidth = equalizerCanvas.width * 1.0 / bufferLength;
    let x = 0;

    for (let i = 0; i < bufferLength; i++) {
      const v = timeDomainDataArray[i] / 128.0;
      const y = v * equalizerCanvas.height / 2;

      canvasCtx.beginPath();
      canvasCtx.arc(x, y, 2, 0, Math.PI * 2, false); // Draw a small circle (dot)
      canvasCtx.fillStyle = '#1a73e8';
      canvasCtx.fill();
      x += sliceWidth;
    }
  }

  // 5. Simple Blob/Pulse Effect (based on overall frequency energy)
  function drawBlobEffect() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const baseRadius = Math.min(centerX, centerY) * 0.2; // Base size of the blob

    // Calculate average frequency energy
    let sum = 0;
    for (let i = 0; i < bufferLength; i++) {
      sum += frequencyDataArray[i];
    }
    const averageFrequency = sum / bufferLength;
    const scale = (averageFrequency / 255) * 0.8 + 0.2; // Scale from 0.2 to 1 for subtle movement

    const currentRadius = baseRadius * scale * 2; // Make it more pronounced

    canvasCtx.beginPath();
    canvasCtx.arc(centerX, centerY, currentRadius, 0, Math.PI * 2, false);
    canvasCtx.fillStyle = 'rgba(26, 115, 232, 0.7)'; // Semi-transparent blue
    canvasCtx.fill();
    canvasCtx.strokeStyle = '#1a73e8';
    canvasCtx.lineWidth = 3;
    canvasCtx.stroke();
  }

  // 6. Audio Spectrum Visualizer (Enhanced Frequency Bars)
  function drawAudioSpectrumVisualizer() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const barsToDraw = 128;
    const barWidth = (equalizerCanvas.width / barsToDraw) * 0.9;
    const sliceSize = Math.floor(bufferLength / barsToDraw);

    for (let i = 0; i < barsToDraw; i++) {
      let sum = 0;
      for (let j = 0; j < sliceSize; j++) {
        sum += frequencyDataArray[i * sliceSize + j];
      }
      const averageFrequency = sum / sliceSize;

      let barHeight = (averageFrequency / 255) * equalizerCanvas.height;

      const x = i * (barWidth + (equalizerCanvas.width * 0.01 / barsToDraw));
      const y = equalizerCanvas.height - barHeight;

      const gradient = canvasCtx.createLinearGradient(x, equalizerCanvas.height, x, y);
      gradient.addColorStop(0, 'rgba(26, 115, 232, 0.1)');
      gradient.addColorStop(0.5, 'rgba(26, 115, 232, 0.7)');
      gradient.addColorStop(1, '#1a73e8');
      canvasCtx.fillStyle = gradient;

      canvasCtx.fillRect(x, y, barWidth, barHeight);

      canvasCtx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
      canvasCtx.lineWidth = 0.5;
      canvasCtx.strokeRect(x, y, barWidth, barHeight);
    }
  }

  // 7. Soundwave (Filled Waveform)
  function drawSoundwave() {
    analyser.getByteTimeDomainData(timeDomainDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    canvasCtx.lineWidth = 2;
    canvasCtx.strokeStyle = '#1a73e8';
    canvasCtx.fillStyle = 'rgba(26, 115, 232, 0.3)';

    canvasCtx.beginPath();
    const sliceWidth = equalizerCanvas.width * 1.0 / bufferLength;
    let x = 0;

    canvasCtx.moveTo(0, equalizerCanvas.height);

    for (let i = 0; i < bufferLength; i++) {
      const v = timeDomainDataArray[i] / 128.0;
      const y = v * equalizerCanvas.height / 2;
      canvasCtx.lineTo(x, y);
      x += sliceWidth;
    }

    canvasCtx.lineTo(equalizerCanvas.width, equalizerCanvas.height);
    canvasCtx.closePath();

    canvasCtx.fill();
    canvasCtx.stroke();
  }

  // 8. Visual Equalizer (Circular Frequency Bars)
  function drawVisualEqualizer() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const maxRadius = Math.min(centerX, centerY) * 0.8;
    const minRadius = maxRadius * 0.2;

    const barCount = 60;
    const angleStep = (Math.PI * 2) / barCount;
    const sliceSize = Math.floor(bufferLength / barCount);

    for (let i = 0; i < barCount; i++) {
      let sum = 0;
      for (let j = 0; j < sliceSize; j++) {
        sum += frequencyDataArray[i * sliceSize + j];
      }
      const averageFrequency = sum / sliceSize;

      const barHeight = (averageFrequency / 255) * (maxRadius - minRadius);
      const currentRadius = minRadius + barHeight;

      const startAngle = i * angleStep;
      const endAngle = (i + 1) * angleStep - 0.02;

      const x1 = centerX + minRadius * Math.cos(startAngle);
      const y1 = centerY + minRadius * Math.sin(startAngle);

      const x2 = centerX + currentRadius * Math.cos(startAngle);
      const y2 = centerY + currentRadius * Math.sin(startAngle);

      const x3 = centerX + currentRadius * Math.cos(endAngle);
      const y3 = centerY + currentRadius * Math.sin(endAngle);

      const x4 = centerX + minRadius * Math.cos(endAngle);
      const y4 = centerY + minRadius * Math.sin(endAngle);

      const gradient = canvasCtx.createLinearGradient(x1, y1, x2, y2);
      gradient.addColorStop(0, 'rgba(26, 115, 232, 0.3)');
      gradient.addColorStop(1, '#1a73e8');
      canvasCtx.fillStyle = gradient;

      canvasCtx.beginPath();
      canvasCtx.moveTo(x1, y1);
      canvasCtx.lineTo(x2, y2);
      canvasCtx.arc(centerX, centerY, currentRadius, startAngle, endAngle, false);
      canvasCtx.lineTo(x4, y4);
      canvasCtx.arc(centerX, centerY, minRadius, endAngle, startAngle, true);
      canvasCtx.closePath();
      canvasCtx.fill();

      canvasCtx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
      canvasCtx.lineWidth = 0.5;
      canvasCtx.stroke();
    }
  }

  // 9. WaveSurfer-like (Amplitude Peaks - Vertical Lines)
  function drawWavesurferLike() {
    analyser.getByteTimeDomainData(timeDomainDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerY = equalizerCanvas.height / 2;
    const barWidth = 2;
    const gap = 1;
    let x = 0;

    const numberOfBars = equalizerCanvas.width / (barWidth + gap);
    const step = Math.ceil(bufferLength / numberOfBars);

    for (let i = 0; i < bufferLength; i += step) {
      const v = (timeDomainDataArray[i] / 128.0) - 1;
      const height = Math.abs(v) * equalizerCanvas.height * 0.8;

      const topY = centerY - (height / 2);

      canvasCtx.fillStyle = '#1a73e8';
      canvasCtx.fillRect(x, topY, barWidth, height);
      x += barWidth + gap;
    }
  }

  // 10. Audio Sound Graphic (Reactive Circle)
  function drawAudioSoundGraphic() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const baseRadius = Math.min(centerX, centerY) * 0.15;

    let sum = 0;
    for (let i = 0; i < bufferLength; i++) {
      sum += frequencyDataArray[i];
    }
    const averageFrequency = sum / bufferLength;

    const scale = (averageFrequency / 255);
    const currentRadius = baseRadius + (baseRadius * 1.5 * scale);

    const r = Math.floor(26 + (255 - 26) * scale);
    const g = Math.floor(115 + (255 - 115) * scale);
    const b = Math.floor(232 + (255 - 232) * scale);
    canvasCtx.fillStyle = `rgba(${r}, ${g}, ${b}, 0.8)`;
    canvasCtx.strokeStyle = `rgb(${r}, ${g}, ${b})`;
    canvasCtx.lineWidth = 3;

    canvasCtx.beginPath();
    canvasCtx.arc(centerX, centerY, currentRadius, 0, Math.PI * 2, false);
    canvasCtx.fill();
    canvasCtx.stroke();

    canvasCtx.shadowBlur = 10 + (20 * scale);
    canvasCtx.shadowColor = `rgba(${r}, ${g}, ${b}, 0.5)`;
    canvasCtx.fill();
    canvasCtx.shadowBlur = 0;
  }

  // 11. Vertical Symmetrical Bars
  function drawVerticalSymmetricalBars() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const barWidth = equalizerCanvas.width / bufferLength * 1.5;
    let x = 0;
    const centerY = equalizerCanvas.height / 2;

    for (let i = 0; i < bufferLength; i++) {
      const barHeight = frequencyDataArray[i] / 255 * (equalizerCanvas.height / 2);

      const gradient = canvasCtx.createLinearGradient(x, centerY - barHeight, x, centerY + barHeight);
      gradient.addColorStop(0, 'rgba(26, 115, 232, 0.1)');
      gradient.addColorStop(0.5, '#1a73e8');
      gradient.addColorStop(1, 'rgba(26, 115, 232, 0.1)');
      canvasCtx.fillStyle = gradient;

      canvasCtx.fillRect(x, centerY - barHeight, barWidth, barHeight * 2);
      x += barWidth + 1;
    }
  }

  // 12. Particle Cloud
  function drawParticleCloud() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const maxParticleSize = 5;
    const minParticleSize = 1;

    for (let i = 0; i < bufferLength; i += 5) {
      const data = frequencyDataArray[i];
      const normalizedData = data / 255;

      const angle = (i / bufferLength) * Math.PI * 2;
      const radius = Math.min(centerX, centerY) * normalizedData;

      const x = centerX + radius * Math.cos(angle);
      const y = centerY + radius * Math.sin(angle);

      const size = minParticleSize + (maxParticleSize - minParticleSize) * normalizedData;

      canvasCtx.beginPath();
      canvasCtx.arc(x, y, size, 0, Math.PI * 2, false);
      canvasCtx.fillStyle = `rgba(26, 115, 232, ${0.2 + normalizedData * 0.8})`;
      canvasCtx.fill();
    }
  }

  // 13. Rotating Ring
  function drawRotatingRing() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const baseRadius = Math.min(centerX, centerY) * 0.3;
    const numSegments = 120;
    const segmentWidth = (Math.PI * 2) / numSegments;

    rotationAngle += 0.005;

    canvasCtx.save();
    canvasCtx.translate(centerX, centerY);
    canvasCtx.rotate(rotationAngle);

    for (let i = 0; i < numSegments; i++) {
      const dataIndex = Math.floor(i * (bufferLength / numSegments));
      const barHeight = (frequencyDataArray[dataIndex] / 255) * baseRadius * 1.5;

      const angle = i * segmentWidth;
      const x1 = baseRadius * Math.cos(angle);
      const y1 = baseRadius * Math.sin(angle);

      const x2 = (baseRadius + barHeight) * Math.cos(angle);
      const y2 = (baseRadius + barHeight) * Math.sin(angle);

      canvasCtx.beginPath();
      canvasCtx.moveTo(x1, y1);
      canvasCtx.lineTo(x2, y2);
      canvasCtx.strokeStyle = `hsl(${i * (360 / numSegments)}, 80%, 60%)`;
      canvasCtx.lineWidth = 2;
      canvasCtx.stroke();
    }
    canvasCtx.restore();
  }

  // 14. Abstract Lines
  function drawAbstractLines() {
    analyser.getByteTimeDomainData(timeDomainDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    canvasCtx.lineWidth = 1.5;
    canvasCtx.strokeStyle = '#66CCFF';

    const sliceWidth = equalizerCanvas.width * 1.0 / bufferLength;
    let x = 0;

    for (let i = 0; i < bufferLength; i++) {
      const v = timeDomainDataArray[i] / 128.0;
      const y = v * equalizerCanvas.height / 2;

      canvasCtx.beginPath();
      canvasCtx.moveTo(x, equalizerCanvas.height / 2);
      canvasCtx.lineTo(x, y);
      canvasCtx.stroke();

      x += sliceWidth;
    }
  }

  // 15. Sphere Pulse
  function drawSpherePulse() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const baseRadius = Math.min(centerX, centerY) * 0.1;

    let sum = 0;
    for (let i = 0; i < bufferLength; i++) {
      sum += frequencyDataArray[i];
    }
    const averageFrequency = sum / bufferLength;
    const scale = (averageFrequency / 255);

    const currentRadius = baseRadius + (baseRadius * 2 * scale);
    const alpha = 0.5 + (scale * 0.5);

    const r = Math.floor(26 + (255 - 26) * scale);
    const g = Math.floor(115 + (180 - 115) * scale);
    const b = Math.floor(232 + (255 - 232) * scale);

    canvasCtx.fillStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
    canvasCtx.strokeStyle = `rgb(${r}, ${g}, ${b})`;
    canvasCtx.lineWidth = 5 + (scale * 5);

    canvasCtx.beginPath();
    canvasCtx.arc(centerX, centerY, currentRadius, 0, Math.PI * 2, false);
    canvasCtx.fill();
    canvasCtx.stroke();

    canvasCtx.shadowBlur = 15 + (scale * 20);
    canvasCtx.shadowColor = `rgba(${r}, ${g}, ${b}, ${alpha * 0.7})`;
    canvasCtx.fill();
    canvasCtx.shadowBlur = 0;
  }

  // 16. Line Grid
  function drawLineGrid() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const gridSize = 20;
    const numX = Math.ceil(equalizerCanvas.width / gridSize);
    const numY = Math.ceil(equalizerCanvas.height / gridSize);

    canvasCtx.strokeStyle = 'rgba(255, 255, 255, 0.1)';

    for (let i = 0; i < numX; i++) {
      for (let j = 0; j < numY; j++) {
        const x = i * gridSize;
        const y = j * gridSize;

        const dataIndex = Math.floor((i / numX) * bufferLength);
        const intensity = frequencyDataArray[dataIndex] / 255;

        const offsetX = (intensity - 0.5) * gridSize * 0.5;
        const offsetY = (intensity - 0.5) * gridSize * 0.5;

        canvasCtx.beginPath();
        canvasCtx.moveTo(x + offsetX, y);
        canvasCtx.lineTo(x + gridSize + offsetX, y);
        canvasCtx.lineTo(x + gridSize, y + gridSize + offsetY);
        canvasCtx.lineTo(x, y + gridSize + offsetY);
        canvasCtx.closePath();
        canvasCtx.stroke();
      }
    }
  }

  // 17. Growing Circles
  function drawGrowingCircles() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const maxRadius = Math.min(centerX, centerY) * 0.9;
    const numCircles = 5;

    for (let i = 0; i < numCircles; i++) {
      const dataIndex = Math.floor((i / numCircles) * bufferLength);
      const intensity = frequencyDataArray[dataIndex] / 255;

      const radius = maxRadius * (0.2 + intensity * 0.8 * (1 - (i / numCircles)));
      const alpha = 0.1 + intensity * 0.4;

      canvasCtx.beginPath();
      canvasCtx.arc(centerX, centerY, radius, 0, Math.PI * 2, false);
      canvasCtx.strokeStyle = `rgba(26, 115, 232, ${alpha})`;
      canvasCtx.lineWidth = 1 + (intensity * 3);
      canvasCtx.stroke();
    }
  }

  // 18. Raindrop Effect
  function drawRaindropEffect() {
    analyser.getByteFrequencyData(frequencyDataArray);

    // Initialize particles if they are empty (happens on effect switch)
    if (particles.length === 0) {
      const maxParticles = 100;
      for (let i = 0; i < maxParticles; i++) {
        particles.push({
          x: Math.random() * equalizerCanvas.width,
          y: Math.random() * equalizerCanvas.height,
          size: Math.random() * 2 + 1,
          speed: Math.random() * 1 + 0.5,
          alpha: Math.random()
        });
      }
    }

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    let sum = 0;
    for (let i = 0; i < bufferLength; i++) {
      sum += frequencyDataArray[i];
    }
    const averageFrequency = sum / bufferLength;
    const pulseScale = (averageFrequency / 255);

    for (let i = 0; i < particles.length; i++) {
      const p = particles[i];

      p.y += p.speed * (1 + pulseScale * 2);
      p.alpha -= 0.005;

      if (p.y > equalizerCanvas.height || p.alpha <= 0) {
        p.x = Math.random() * equalizerCanvas.width;
        p.y = -p.size;
        p.size = Math.random() * 2 + 1;
        p.speed = Math.random() * 1 + 0.5;
        p.alpha = 1;
      }

      canvasCtx.beginPath();
      canvasCtx.arc(p.x, p.y, p.size * (1 + pulseScale), 0, Math.PI * 2, false);
      canvasCtx.fillStyle = `rgba(26, 115, 232, ${p.alpha})`;
      canvasCtx.fill();
    }
  }

  // 19. Tunnel Effect
  function drawTunnelEffect() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const maxDimension = Math.max(equalizerCanvas.width, equalizerCanvas.height);
    const numRings = 20;

    tunnelOffset = (tunnelOffset + 0.5) % maxDimension;

    for (let i = 0; i < numRings; i++) {
      const dataIndex = Math.floor((i / numRings) * bufferLength);
      const intensity = frequencyDataArray[dataIndex] / 255;

      const radius = (maxDimension * (i / numRings)) + tunnelOffset;
      const actualRadius = radius % maxDimension;

      const pulseStrength = (intensity * 0.5) + 0.5;
      const finalRadius = actualRadius * pulseStrength;

      const alpha = 1 - (actualRadius / maxDimension);

      canvasCtx.beginPath();
      canvasCtx.arc(centerX, centerY, finalRadius, 0, Math.PI * 2, false);
      canvasCtx.strokeStyle = `rgba(26, 115, 232, ${alpha})`;
      canvasCtx.lineWidth = 1 + (intensity * 2);
      canvasCtx.stroke();
    }
  }

  // 20. Fireflies / Swarm
  function drawFirefliesSwarm() {
    analyser.getByteFrequencyData(frequencyDataArray);

    // Initialize fireflies if they are empty
    if (fireflies.length === 0) {
      const numFireflies = 80;
      for (let i = 0; i < numFireflies; i++) {
        fireflies.push({
          x: Math.random() * equalizerCanvas.width,
          y: Math.random() * equalizerCanvas.height,
          vx: (Math.random() - 0.5) * 2,
          vy: (Math.random() - 0.5) * 2,
          size: Math.random() * 1.5 + 0.5,
          life: Math.random() * 100,
          maxLife: 100
        });
      }
    }

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    let sum = 0;
    for (let i = 0; i < bufferLength; i++) {
      sum += frequencyDataArray[i];
    }
    const averageEnergy = sum / bufferLength;
    const influence = averageEnergy / 255;

    for (let i = 0; i < fireflies.length; i++) {
      const f = fireflies[i];

      f.x += f.vx * (1 + influence);
      f.y += f.vy * (1 + influence);

      f.vx += (Math.random() - 0.5) * 0.5 * influence;
      f.vy += (Math.random() - 0.5) * 0.5 * influence;

      if (f.x < 0 || f.x > equalizerCanvas.width) f.vx *= -1;
      if (f.y < 0 || f.y > equalizerCanvas.height) f.vy *= -1;

      f.life--;
      if (f.life <= 0) {
        f.x = Math.random() * equalizerCanvas.width;
        f.y = Math.random() * equalizerCanvas.height;
        f.vx = (Math.random() - 0.5) * 2;
        f.vy = (Math.random() - 0.5) * 2;
        f.size = Math.random() * 1.5 + 0.5;
        f.life = f.maxLife;
      }

      const alpha = (f.life / f.maxLife) * (0.5 + influence * 0.5);
      canvasCtx.beginPath();
      canvasCtx.arc(f.x, f.y, f.size * (1 + influence), 0, Math.PI * 2, false);
      canvasCtx.fillStyle = `rgba(255, 255, 100, ${alpha})`;
      canvasCtx.fill();

      canvasCtx.shadowBlur = f.size * 5 * influence;
      canvasCtx.shadowColor = `rgba(255, 255, 100, ${alpha})`;
      canvasCtx.fill();
      canvasCtx.shadowBlur = 0;
    }
  }

  // 21. Pixel Grid / Heatmap
  function drawPixelGridHeatmap() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const pixelSize = 10;
    const numXPixels = Math.ceil(equalizerCanvas.width / pixelSize);
    const numYPixels = Math.ceil(equalizerCanvas.height / pixelSize);

    for (let i = 0; i < numXPixels; i++) {
      for (let j = 0; j < numYPixels; j++) {
        const x = i * pixelSize;
        const y = j * pixelSize;

        const dataIndex = Math.floor((i / numXPixels) * bufferLength);
        const intensity = frequencyDataArray[dataIndex] / 255;

        const r = Math.floor(26 + (255 - 26) * intensity);
        const g = Math.floor(115 + (0 - 115) * intensity);
        const b = Math.floor(232 + (0 - 232) * intensity);

        canvasCtx.fillStyle = `rgb(${r}, ${g}, ${b})`;
        canvasCtx.fillRect(x, y, pixelSize, pixelSize);
      }
    }
  }

  // 22. Lissajous Curve (simplified with audio influence)
  function drawLissajousCurve() {
    analyser.getByteTimeDomainData(timeDomainDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const amplitudeX = centerX * 0.8;
    const amplitudeY = centerY * 0.8;

    canvasCtx.beginPath();
    canvasCtx.strokeStyle = '#FF66FF';
    canvasCtx.lineWidth = 1.5;

    const freqA = 2;
    const freqB = 3;
    const phaseShift = Math.PI / 2;

    for (let i = 0; i < bufferLength; i++) {
      const v1 = timeDomainDataArray[i] / 128.0 - 1;
      const v2 = timeDomainDataArray[(i + Math.floor(bufferLength / 4)) % bufferLength] / 128.0 - 1;

      const x = centerX + amplitudeX * Math.cos(i * 0.05 + v1 * freqA);
      const y = centerY + amplitudeY * Math.sin(i * 0.05 + v2 * freqB + phaseShift);

      if (i === 0) {
        canvasCtx.moveTo(x, y);
      } else {
        canvasCtx.lineTo(x, y);
      }
    }
    canvasCtx.stroke();
  }

  // 23. Ripple Effect (Concentric Expanding Circles)
  function drawRippleEffect() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const maxRadius = Math.min(centerX, centerY) * 0.9;

    let sum = 0;
    for (let i = 0; i < bufferLength; i++) {
      sum += frequencyDataArray[i];
    }
    const averageFrequency = sum / bufferLength;
    const intensity = averageFrequency / 255; // Normalize to 0-1

    const numRipples = 5;
    for (let i = 0; i < numRipples; i++) {
      // Calculate radius based on intensity and a phase offset
      const phaseOffset = (performance.now() * 0.001 * 0.1) % 1; // Slow, continuous expansion
      let currentRadius = maxRadius * ((i / numRipples) + phaseOffset) % maxRadius;

      // Apply intensity for a 'pulse' effect
      currentRadius += currentRadius * intensity * 0.5;

      const alpha = 1 - (currentRadius / maxRadius) * 0.8; // Fade out as it expands

      canvasCtx.beginPath();
      canvasCtx.arc(centerX, centerY, currentRadius, 0, Math.PI * 2, false);
      canvasCtx.strokeStyle = `rgba(26, 115, 232, ${alpha})`;
      canvasCtx.lineWidth = 2 + (intensity * 3); // Thicker line with more intensity
      canvasCtx.stroke();
    }
  }

  // 24. Glow Trails (Smooth, Fading Lines)
  function drawGlowTrails() {
    analyser.getByteTimeDomainData(timeDomainDataArray);

    // Partial clear for trails
    canvasCtx.fillStyle = 'rgba(0, 0, 0, 0.08)'; // Adjust transparency for longer/shorter trails
    canvasCtx.fillRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    canvasCtx.lineWidth = 2;
    canvasCtx.strokeStyle = '#FF6600'; // Orange glow color
    canvasCtx.shadowBlur = 10;
    canvasCtx.shadowColor = '#FF6600';

    const sliceWidth = equalizerCanvas.width * 1.0 / bufferLength;
    let x = 0;

    canvasCtx.beginPath();
    canvasCtx.moveTo(0, equalizerCanvas.height / 2);

    for (let i = 0; i < bufferLength; i++) {
      const v = timeDomainDataArray[i] / 128.0;
      const y = v * equalizerCanvas.height / 2;
      canvasCtx.lineTo(x, y);
      x += sliceWidth;
    }
    canvasCtx.stroke();

    canvasCtx.shadowBlur = 0; // Reset shadow for other drawings
  }

  // 25. Rain and Lightning (Particles + Impulse Lines)
  function initializeLightningParticles() {
    lightningParticles = [];
    const maxLightningParticles = 150; // More particles for a rain effect
    for (let i = 0; i < maxLightningParticles; i++) {
      lightningParticles.push({
        x: Math.random() * equalizerCanvas.width,
        y: Math.random() * equalizerCanvas.height,
        size: Math.random() * 1.5 + 0.5,
        speed: Math.random() * 2 + 1,
        alpha: Math.random() * 0.5 + 0.3 // Slightly less opaque
      });
    }
  }

  function drawRainAndLightning() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    let sum = 0;
    for (let i = 0; i < bufferLength; i++) {
      sum += frequencyDataArray[i];
    }
    const averageFrequency = sum / bufferLength;
    const pulseThreshold = 150; // Adjust for sensitivity of lightning
    const isLightningPulse = averageFrequency > pulseThreshold;
    const intensity = averageFrequency / 255;

    // Draw rain particles
    for (let i = 0; i < lightningParticles.length; i++) {
      const p = lightningParticles[i];

      p.y += p.speed * (1 + intensity * 0.5); // Rain speed slightly influenced by audio
      p.alpha -= 0.003; // Fade out

      if (p.y > equalizerCanvas.height + p.size || p.alpha <= 0) {
        p.x = Math.random() * equalizerCanvas.width;
        p.y = -p.size;
        p.size = Math.random() * 1.5 + 0.5;
        p.speed = Math.random() * 2 + 1;
        p.alpha = Math.random() * 0.5 + 0.3;
      }

      canvasCtx.beginPath();
      canvasCtx.arc(p.x, p.y, p.size, 0, Math.PI * 2, false);
      canvasCtx.fillStyle = `rgba(150, 200, 255, ${p.alpha})`; // Light blue for rain
      canvasCtx.fill();
    }

    // Draw lightning if pulse is detected
    if (isLightningPulse) {
      const lightningCount = Math.floor(intensity * 5); // More lightning for stronger pulses
      for (let i = 0; i < lightningCount; i++) {
        const lightningX = Math.random() * equalizerCanvas.width;
        const lightningWidth = Math.random() * 3 + 1; // Random width
        const lightningHeight = equalizerCanvas.height * (0.5 + Math.random() * 0.5); // Random height

        canvasCtx.beginPath();
        canvasCtx.moveTo(lightningX, 0);
        canvasCtx.lineTo(lightningX + lightningWidth / 2, lightningHeight / 2);
        canvasCtx.lineTo(lightningX - lightningWidth / 2, lightningHeight * 0.75);
        canvasCtx.lineTo(lightningX + lightningWidth / 4, equalizerCanvas.height);
        canvasCtx.strokeStyle = 'rgba(255, 255, 255, 0.9)'; // Bright white
        canvasCtx.lineWidth = 2 + (Math.random() * 3);
        canvasCtx.shadowBlur = 20 + (intensity * 30);
        canvasCtx.shadowColor = 'rgba(200, 200, 255, 0.8)';
        canvasCtx.stroke();
        canvasCtx.shadowBlur = 0;
      }
    }
  }

  // 26. Data Stream (Vertical Lines with Horizontal Flow)
  function drawDataStream() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const segmentHeight = 10;
    const gap = 2;
    const numSegmentsPerColumn = Math.floor(equalizerCanvas.height / (segmentHeight + gap));
    const columnWidth = 5;
    const columnGap = 10;

    // Add a slight horizontal shift over time for flow effect
    const flowOffset = (performance.now() * 0.001 * 5) % (columnWidth + columnGap); // Faster flow

    for (let i = 0; i < bufferLength; i += 5) { // Sample less data for wider columns
      const data = frequencyDataArray[i];
      const intensity = data / 255;

      let x = i * (columnWidth + columnGap) / 5 + flowOffset; // Adjust x with flowOffset

      if (x > equalizerCanvas.width + columnWidth) {
        // Wrap around
        x -= (equalizerCanvas.width + columnWidth + columnGap); // Reset to beginning for smooth loop
      }

      // Draw multiple vertical segments in each column
      for (let j = 0; j < numSegmentsPerColumn; j++) {
        let y = j * (segmentHeight + gap);

        // Make segments appear or disappear based on intensity
        if (Math.random() < intensity * 0.8) { // Higher intensity, more likely to draw
          const gradient = canvasCtx.createLinearGradient(x, y, x + columnWidth, y + segmentHeight);
          gradient.addColorStop(0, 'rgba(0, 200, 255, 0.2)');
          gradient.addColorStop(0.5, 'rgba(0, 200, 255, 0.8)');
          gradient.addColorStop(1, 'rgba(0, 200, 255, 0.2)');
          canvasCtx.fillStyle = gradient;

          canvasCtx.fillRect(x, y, columnWidth, segmentHeight);
        }
      }
    }
  }

  // 27. Vortex Spectrum (Rotating, Expanding Bars)
  function drawVortexSpectrum() {
    analyser.getByteFrequencyData(frequencyDataArray);

    canvasCtx.clearRect(0, 0, equalizerCanvas.width, equalizerCanvas.height);

    const centerX = equalizerCanvas.width / 2;
    const centerY = equalizerCanvas.height / 2;
    const maxRadius = Math.min(centerX, centerY) * 0.9;
    const baseInnerRadius = maxRadius * 0.1; // Inner circle where bars start

    const barCount = 90; // Number of bars in the vortex
    const angleStep = (Math.PI * 2) / barCount;
    const sliceSize = Math.floor(bufferLength / barCount);

    vortexAngle += 0.008; // Continuous rotation speed

    canvasCtx.save();
    canvasCtx.translate(centerX, centerY);
    canvasCtx.rotate(vortexAngle);

    for (let i = 0; i < barCount; i++) {
      let sum = 0;
      for (let j = 0; j < sliceSize; j++) {
        sum += frequencyDataArray[i * sliceSize + j];
      }
      const averageFrequency = sum / sliceSize;
      const barLength = (averageFrequency / 255) * (maxRadius - baseInnerRadius);

      const startAngle = i * angleStep;
      const endAngle = (i + 1) * angleStep - 0.01; // Small gap between bars

      // Radial gradient for each bar
      const gradient = canvasCtx.createRadialGradient(0, 0, baseInnerRadius, 0, 0, baseInnerRadius + barLength);
      gradient.addColorStop(0, 'rgba(0, 255, 255, 0.2)'); // Cyan starting
      gradient.addColorStop(0.5, 'rgba(0, 255, 255, 0.8)');
      gradient.addColorStop(1, 'rgba(0, 100, 255, 1)'); // Darker blue at end

      canvasCtx.fillStyle = gradient;

      canvasCtx.beginPath();
      canvasCtx.arc(0, 0, baseInnerRadius + barLength, startAngle, endAngle, false); // Outer arc
      canvasCtx.arc(0, 0, baseInnerRadius, endAngle, startAngle, true); // Inner arc (reversed)
      canvasCtx.closePath();
      canvasCtx.fill();
    }
    canvasCtx.restore();
  }

  /**
   * Resets the internal state of effects that manage their own particles/arrays.
   * This should be called when switching between effects that have persistent state.
   */
  function resetEffectStates() {
    rotationAngle = 0;
    particles = [];
    fireflies = [];
    tunnelOffset = 0;
    vortexAngle = 0;
    lightningParticles = []; // Ensure this is also reset
  }

  // Expose functions
  exports.EqualizerEffects = {
    init: initEqualizerEffects,
    resetStates: resetEffectStates,
    animate: animate, // This will now handle the requestAnimationFrame loop
    effects: {
      waveform: drawWaveform,
      frequency: drawFrequencyBars,
      circular: drawCircularWaveform,
      dots: drawDotWaveform,
      blob: drawBlobEffect,
      audioSpectrumVisualizer: drawAudioSpectrumVisualizer,
      soundwave: drawSoundwave,
      visualEqualizer: drawVisualEqualizer,
      wavesurferLike: drawWavesurferLike,
      audioSoundGraphic: drawAudioSoundGraphic,
      verticalSymmetricalBars: drawVerticalSymmetricalBars,
      particleCloud: drawParticleCloud,
      rotatingRing: drawRotatingRing,
      abstractLines: drawAbstractLines,
      spherePulse: drawSpherePulse,
      lineGrid: drawLineGrid,
      growingCircles: drawGrowingCircles,
      raindropEffect: drawRaindropEffect,
      tunnelEffect: drawTunnelEffect,
      firefliesSwarm: drawFirefliesSwarm,
      pixelGridHeatmap: drawPixelGridHeatmap,
      lissajousCurve: drawLissajousCurve,
      rippleEffect: drawRippleEffect,
      glowTrails: drawGlowTrails,
      rainAndLightning: drawRainAndLightning,
      dataStream: drawDataStream,
      vortexSpectrum: drawVortexSpectrum
    }
  };

})(window); // Expose EqualizerEffects globally or within a specific namespace