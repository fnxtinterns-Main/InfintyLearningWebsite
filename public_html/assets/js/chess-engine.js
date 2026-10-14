/**
 * INFINITY LEARNING — Rising Rooks Chess Academy Engine & Board Controller v2.0
 * Features:
 * - Real Stockfish 10 Web Worker integration with UCI Skill Level calibration (100, 750, 1250, 2000, 2700)
 * - Live Evaluation Bar streaming real-time advantage (+2.4, -1.8, M3)
 * - Best Move / Tactical Hint engine
 * - 5 Radically Distinct Visual Board & Piece Themes (Neo Emerald, Royal Wood, Cyber Neon, Gothic Crimson, Ocean Blue)
 * - Web Audio API synthesized move, capture, and check sound effects
 * - PeSTO Piece-Square Tables (PST) + Alpha-Beta Minimax Quiescence fallback
 * - Grandmaster Opening Repertoire Book
 */

(function (window) {
  'use strict';

  // ---------------------------------------------------------------------------
  // WEB AUDIO API SOUND EFFECTS (Zero external audio files required)
  // ---------------------------------------------------------------------------
  const ChessAudio = {
    ctx: null,

    init() {
      if (!this.ctx && (window.AudioContext || window.webkitAudioContext)) {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        this.ctx = new AudioCtx();
      }
    },

    playMove() {
      try {
        this.init();
        if (!this.ctx) return;
        if (this.ctx.state === 'suspended') this.ctx.resume();

        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(440, this.ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(120, this.ctx.currentTime + 0.08);

        gain.gain.setValueAtTime(0.2, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.08);

        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.start();
        osc.stop(this.ctx.currentTime + 0.09);
      } catch (e) {}
    },

    playCapture() {
      try {
        this.init();
        if (!this.ctx) return;
        if (this.ctx.state === 'suspended') this.ctx.resume();

        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(320, this.ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(80, this.ctx.currentTime + 0.12);

        gain.gain.setValueAtTime(0.35, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.12);

        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.start();
        osc.stop(this.ctx.currentTime + 0.13);
      } catch (e) {}
    },

    playCheck() {
      try {
        this.init();
        if (!this.ctx) return;
        if (this.ctx.state === 'suspended') this.ctx.resume();

        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, this.ctx.currentTime);
        osc.frequency.setValueAtTime(1320, this.ctx.currentTime + 0.06);

        gain.gain.setValueAtTime(0.25, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.16);

        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.start();
        osc.stop(this.ctx.currentTime + 0.17);
      } catch (e) {}
    }
  };

  // ---------------------------------------------------------------------------
  // PIECE SVG THEMES (5 Radically Distinct Sets)
  // ---------------------------------------------------------------------------
  const PIECE_THEMES = {
    // 1. NEO EMERALD (Tournament Crisp Clean Vectors)
    neo_emerald: {
      P: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#ffffff" stroke="#222" stroke-width="1.8" stroke-linejoin="round"/></svg>',
      N: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#ffffff" stroke="#222" stroke-width="1.8"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#ffffff" stroke="#222" stroke-width="1.8"/><circle cx="15" cy="14" r="1.5" fill="#222"/></svg>',
      B: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#222" stroke-width="1.8" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#ffffff"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#ffffff"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#ffffff"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5"/></g></svg>',
      R: '<svg viewBox="0 0 45 45"><g fill="#ffffff" stroke="#222" stroke-width="1.8" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      Q: '<svg viewBox="0 0 45 45"><g fill="#ffffff" stroke="#222" stroke-width="1.8" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2"/><circle cx="14" cy="9" r="2"/><circle cx="22.5" cy="8" r="2"/><circle cx="31" cy="9" r="2"/><circle cx="39" cy="12" r="2"/></g></svg>',
      K: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#222" stroke-width="1.8" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#ffffff"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#ffffff"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0"/></g></svg>',
      p: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#1e293b" stroke="#000" stroke-width="1.8" stroke-linejoin="round"/></svg>',
      n: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#1e293b" stroke="#000" stroke-width="1.8"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#1e293b" stroke="#000" stroke-width="1.8"/><circle cx="15" cy="14" r="1.5" fill="#fff"/></svg>',
      b: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#000" stroke-width="1.8" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#1e293b"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#1e293b"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#1e293b"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5" stroke="#fff"/></g></svg>',
      r: '<svg viewBox="0 0 45 45"><g fill="#1e293b" stroke="#000" stroke-width="1.8" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      q: '<svg viewBox="0 0 45 45"><g fill="#1e293b" stroke="#000" stroke-width="1.8" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2"/><circle cx="14" cy="9" r="2"/><circle cx="22.5" cy="8" r="2"/><circle cx="31" cy="9" r="2"/><circle cx="39" cy="12" r="2"/></g></svg>',
      k: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#000" stroke-width="1.8" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#1e293b"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#1e293b"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0" stroke="#fff"/></g></svg>'
    },

    // 2. ROYAL WOODCRAFT (Luxury Boxwood & Mahogany)
    royal_wood: {
      P: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#fffbeb" stroke="#854d0e" stroke-width="2" stroke-linejoin="round"/></svg>',
      N: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#fffbeb" stroke="#854d0e" stroke-width="2"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#fffbeb" stroke="#854d0e" stroke-width="2"/><circle cx="15" cy="14" r="1.6" fill="#d97706"/></svg>',
      B: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#854d0e" stroke-width="2" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#fffbeb"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#fffbeb"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#fde68a"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5"/></g></svg>',
      R: '<svg viewBox="0 0 45 45"><g fill="#fffbeb" stroke="#854d0e" stroke-width="2" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      Q: '<svg viewBox="0 0 45 45"><g fill="#fffbeb" stroke="#854d0e" stroke-width="2" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2" fill="#d97706"/><circle cx="14" cy="9" r="2" fill="#d97706"/><circle cx="22.5" cy="8" r="2" fill="#d97706"/><circle cx="31" cy="9" r="2" fill="#d97706"/><circle cx="39" cy="12" r="2" fill="#d97706"/></g></svg>',
      K: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#854d0e" stroke-width="2" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#fffbeb"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#fffbeb"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0"/></g></svg>',
      p: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#451a03" stroke="#1c0b02" stroke-width="2" stroke-linejoin="round"/></svg>',
      n: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#451a03" stroke="#1c0b02" stroke-width="2"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#451a03" stroke="#1c0b02" stroke-width="2"/><circle cx="15" cy="14" r="1.6" fill="#fde68a"/></svg>',
      b: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#1c0b02" stroke-width="2" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#451a03"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#451a03"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#d97706"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5" stroke="#fde68a"/></g></svg>',
      r: '<svg viewBox="0 0 45 45"><g fill="#451a03" stroke="#1c0b02" stroke-width="2" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      q: '<svg viewBox="0 0 45 45"><g fill="#451a03" stroke="#1c0b02" stroke-width="2" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2" fill="#d97706"/><circle cx="14" cy="9" r="2" fill="#d97706"/><circle cx="22.5" cy="8" r="2" fill="#d97706"/><circle cx="31" cy="9" r="2" fill="#d97706"/><circle cx="39" cy="12" r="2" fill="#d97706"/></g></svg>',
      k: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#1c0b02" stroke-width="2" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#451a03"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#451a03"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0" stroke="#fde68a"/></g></svg>'
    },

    // 3. CYBER NEON (Futuristic Hologram & Matrix Obsidian)
    cyber_neon: {
      P: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#e0f2fe" stroke="#06b6d4" stroke-width="2.2" stroke-linejoin="round"/></svg>',
      N: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#e0f2fe" stroke="#06b6d4" stroke-width="2.2"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#e0f2fe" stroke="#06b6d4" stroke-width="2.2"/><circle cx="15" cy="14" r="2.2" fill="#0891b2"/></svg>',
      B: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#06b6d4" stroke-width="2.2" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#e0f2fe"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#e0f2fe"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#0891b2"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5"/></g></svg>',
      R: '<svg viewBox="0 0 45 45"><g fill="#e0f2fe" stroke="#06b6d4" stroke-width="2.2" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      Q: '<svg viewBox="0 0 45 45"><g fill="#e0f2fe" stroke="#06b6d4" stroke-width="2.2" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2.2" fill="#ec4899"/><circle cx="14" cy="9" r="2.2" fill="#0891b2"/><circle cx="22.5" cy="8" r="2.5" fill="#ec4899"/><circle cx="31" cy="9" r="2.2" fill="#0891b2"/><circle cx="39" cy="12" r="2.2" fill="#ec4899"/></g></svg>',
      K: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#06b6d4" stroke-width="2.2" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#e0f2fe"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#e0f2fe"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0"/></g></svg>',
      p: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#020617" stroke="#10b981" stroke-width="2" stroke-linejoin="round"/></svg>',
      n: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#020617" stroke="#10b981" stroke-width="2"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#020617" stroke="#10b981" stroke-width="2"/><circle cx="15" cy="14" r="2" fill="#ec4899"/></svg>',
      b: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#10b981" stroke-width="2" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#020617"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#020617"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#10b981"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5" stroke="#ec4899"/></g></svg>',
      r: '<svg viewBox="0 0 45 45"><g fill="#020617" stroke="#10b981" stroke-width="2" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      q: '<svg viewBox="0 0 45 45"><g fill="#020617" stroke="#10b981" stroke-width="2" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2" fill="#ec4899"/><circle cx="14" cy="9" r="2" fill="#10b981"/><circle cx="22.5" cy="8" r="2.5" fill="#ec4899"/><circle cx="31" cy="9" r="2" fill="#10b981"/><circle cx="39" cy="12" r="2" fill="#ec4899"/></g></svg>',
      k: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#10b981" stroke-width="2" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#020617"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#020617"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0" stroke="#ec4899"/></g></svg>'
    },

    // 4. GOTHIC CRIMSON (Iron Citadel & Blood Ruby)
    gothic_crimson: {
      P: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#f5f5f4" stroke="#44403c" stroke-width="2.2" stroke-linejoin="round"/></svg>',
      N: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#f5f5f4" stroke="#44403c" stroke-width="2.2"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#f5f5f4" stroke="#44403c" stroke-width="2.2"/><circle cx="15" cy="14" r="1.8" fill="#ca8a04"/></svg>',
      B: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#44403c" stroke-width="2.2" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#f5f5f4"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#f5f5f4"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#ca8a04"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5"/></g></svg>',
      R: '<svg viewBox="0 0 45 45"><g fill="#f5f5f4" stroke="#44403c" stroke-width="2.2" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      Q: '<svg viewBox="0 0 45 45"><g fill="#f5f5f4" stroke="#44403c" stroke-width="2.2" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2.2" fill="#ca8a04"/><circle cx="14" cy="9" r="2.2" fill="#ca8a04"/><circle cx="22.5" cy="8" r="2.2" fill="#ca8a04"/><circle cx="31" cy="9" r="2.2" fill="#ca8a04"/><circle cx="39" cy="12" r="2.2" fill="#ca8a04"/></g></svg>',
      K: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#44403c" stroke-width="2.2" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#f5f5f4"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#f5f5f4"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0"/></g></svg>',
      p: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#18181b" stroke="#dc2626" stroke-width="2" stroke-linejoin="round"/></svg>',
      n: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#18181b" stroke="#dc2626" stroke-width="2"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#18181b" stroke="#dc2626" stroke-width="2"/><circle cx="15" cy="14" r="1.8" fill="#f87171"/></svg>',
      b: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#dc2626" stroke-width="2" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#18181b"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#18181b"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#dc2626"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5" stroke="#f87171"/></g></svg>',
      r: '<svg viewBox="0 0 45 45"><g fill="#18181b" stroke="#dc2626" stroke-width="2" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      q: '<svg viewBox="0 0 45 45"><g fill="#18181b" stroke="#dc2626" stroke-width="2" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2.2" fill="#ef4444"/><circle cx="14" cy="9" r="2.2" fill="#ef4444"/><circle cx="22.5" cy="8" r="2.2" fill="#ef4444"/><circle cx="31" cy="9" r="2.2" fill="#ef4444"/><circle cx="39" cy="12" r="2.2" fill="#ef4444"/></g></svg>',
      k: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#dc2626" stroke-width="2" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#18181b"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#18181b"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0" stroke="#f87171"/></g></svg>'
    },

    // 5. OCEAN BLUE (Grandmaster Sky & Ice Azure)
    ocean_blue: {
      P: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#ffffff" stroke="#0284c7" stroke-width="2" stroke-linejoin="round"/></svg>',
      N: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#ffffff" stroke="#0284c7" stroke-width="2"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#ffffff" stroke="#0284c7" stroke-width="2"/><circle cx="15" cy="14" r="1.8" fill="#0369a1"/></svg>',
      B: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#0284c7" stroke-width="2" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#ffffff"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#ffffff"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#0284c7"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5"/></g></svg>',
      R: '<svg viewBox="0 0 45 45"><g fill="#ffffff" stroke="#0284c7" stroke-width="2" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      Q: '<svg viewBox="0 0 45 45"><g fill="#ffffff" stroke="#0284c7" stroke-width="2" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2" fill="#0369a1"/><circle cx="14" cy="9" r="2" fill="#0369a1"/><circle cx="22.5" cy="8" r="2.2" fill="#0284c7"/><circle cx="31" cy="9" r="2" fill="#0369a1"/><circle cx="39" cy="12" r="2" fill="#0369a1"/></g></svg>',
      K: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#0284c7" stroke-width="2" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#ffffff"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#ffffff"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0"/></g></svg>',
      p: '<svg viewBox="0 0 45 45"><path d="M22.5 9c-2.21 0-4 1.79-4 4 0 .89.29 1.71.78 2.38C17.33 16.5 16 18.59 16 21c0 2.03.94 3.84 2.41 5.03-3 1.06-7.41 5.55-7.41 13.47h23c0-7.92-4.41-12.41-7.41-13.47 1.47-1.19 2.41-3 2.41-5.03 0-2.41-1.33-4.5-3.28-5.62.49-.67.78-1.49.78-2.38 0-2.21-1.79-4-4-4z" fill="#0f172a" stroke="#38bdf8" stroke-width="2" stroke-linejoin="round"/></svg>',
      n: '<svg viewBox="0 0 45 45"><path d="M22 10c10.5 1 16.5 8 16 29H15c0-9 10-6.5 8-21" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/><path d="M24 18c.38 2.91-5.55 7.37-8 9-3 2-2.82 4.34-5 4-1.042-.94 1.41-3.04 0-3-1 0-.098 2.41-2 2-1-.02-.822-1.92-2-2-.667-.04-.667 1.33-2 1-1 0 0-3 3-4 1.5-.5 3-4 3-7 0-4 4.5-9 13-9z" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/><circle cx="15" cy="14" r="1.8" fill="#38bdf8"/></svg>',
      b: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#38bdf8" stroke-width="2" stroke-linejoin="round"><path d="M9 36c3.39-.97 10.11.43 13.5-2 3.39 2.43 10.11 1.03 13.5 2 0 0 1.65.54 3 2-.68.97-1.65.99-3 .5-3.39-.97-10.11.46-13.5-1-3.39 1.46-10.11.03-13.5 1-1.354.49-2.323.47-3-.5 1.354-1.94 3-2 3-2z" fill="#0f172a"/><path d="M15 32c2.5 2.5 12.5 2.5 15 0 .5-1.5 0-2 0-2 0-2.5-2.5-4-2.5-4 5.5-1.5 6-11.5-5-15.5-11 4-10.5 14-5 15.5 0 0-2.5 1.5-2.5 4 0 0-.5.5 0 2z" fill="#0f172a"/><path d="M25 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 1 1 5 0z" fill="#38bdf8"/><path d="M17.5 26h10M15 30h15M22.5 15.5v5M20 18h5" stroke="#f8fafc"/></g></svg>',
      r: '<svg viewBox="0 0 45 45"><g fill="#0f172a" stroke="#38bdf8" stroke-width="2" stroke-linejoin="round"><path d="M9 39h27v-3H9v3zm3-3v-4.5h21V36H12zm2-4.5c0-4 3-6.5 3-6.5h11s3 2.5 3 6.5H14zm1-8.5l1.5-6h15l1.5 6H15zm-2-8h4v4h-4v-4zm6.5 0h4v4h-4v-4zm6.5 0h4v4h-4v-4zm-15-2h27v-3H10.5v3z"/></g></svg>',
      q: '<svg viewBox="0 0 45 45"><g fill="#0f172a" stroke="#38bdf8" stroke-width="2" stroke-linejoin="round"><path d="M9 26c8.5-1.5 21-1.5 27 0l2-12-7 11-4-15-4.5 15-4.5-15-4 15-7-11 2 12z"/><path d="M9 26c0 2 1.5 2 2.5 4 2.5 4 1 8.5 1 8.5h20s-1.5-4.5 1-8.5c1-2 2.5-2 2.5-4-8.5-1.5-18.5-1.5-27 0z"/><circle cx="6" cy="12" r="2.2" fill="#38bdf8"/><circle cx="14" cy="9" r="2.2" fill="#38bdf8"/><circle cx="22.5" cy="8" r="2.2" fill="#38bdf8"/><circle cx="31" cy="9" r="2.2" fill="#38bdf8"/><circle cx="39" cy="12" r="2.2" fill="#38bdf8"/></g></svg>',
      k: '<svg viewBox="0 0 45 45"><g fill="none" stroke="#38bdf8" stroke-width="2" stroke-linejoin="round"><path d="M22.5 11.63V6M20 8h5" stroke-linejoin="miter"/><path d="M22.5 25s4.5-7.5 3-10.5c0 0-1-2.5-3-2.5s-3 2.5-3 2.5c-1.5 3 3 10.5 3 10.5" fill="#0f172a"/><path d="M11.5 37c5.5 3.5 16.5 3.5 22 0v-7s9-4.5 6-10.5c-4-1-6 2.5-6 2.5s-.5-4-4.5-4c-3.5 0-4 3.5-4 3.5s-.5-3.5-4-3.5c-4 0-4.5 4-4.5 4s-2-3.5-6-2.5c-3 6 6 10.5 6 10.5v7z" fill="#0f172a"/><path d="M11.5 30c5.5-3 16.5-3 22 0M11.5 33.5c5.5-3 16.5-3 22 0M11.5 37c5.5-3 16.5-3 22 0" stroke="#f8fafc"/></g></svg>'
    }
  };

  function getPieceSvg(pieceChar, theme) {
    const t = PIECE_THEMES[theme] || PIECE_THEMES['neo_emerald'];
    return t[pieceChar] || PIECE_THEMES['neo_emerald'][pieceChar] || '';
  }

  // ---------------------------------------------------------------------------
  // REAL STOCKFISH 10 WEB WORKER BRIDGE
  // ---------------------------------------------------------------------------
  class StockfishBridge {
    constructor() {
      this.worker = null;
      this.isReady = false;
      this.isSearching = false;
      this.pendingSearch = null;
      this.onBestMove = null;
      this.onEvalUpdate = null;
      this.initWorker();
    }

    initWorker() {
      try {
        this.worker = new Worker('/assets/js/stockfish.js');
        this.worker.onmessage = (e) => this.onMessage(e.data);
        this.worker.onerror = (err) => {
          console.warn('Stockfish Worker runtime error:', err);
          this.isReady = false;
        };
        this.worker.postMessage('uci');
        this.worker.postMessage('isready');
      } catch (err) {
        console.warn('Stockfish Worker unavailable, using PeSTO Engine fallback:', err);
        this.isReady = false;
      }
    }

    configureStrength(skill, elo) {
      if (!this.isReady || !this.worker) return;
      const s = Math.max(0, Math.min(20, skill));
      this.worker.postMessage('setoption name Skill Level value ' + s);
      // Unleash full grandmaster power for Skill 18+ (Rising Rook & Queen)
      if (s >= 18 || (elo && elo >= 2000)) {
        this.worker.postMessage('setoption name UCI_LimitStrength value false');
      } else if (elo && elo < 2000) {
        this.worker.postMessage('setoption name UCI_LimitStrength value true');
        this.worker.postMessage('setoption name UCI_Elo value ' + elo);
      } else {
        this.worker.postMessage('setoption name UCI_LimitStrength value false');
      }
    }

    analyzePosition(movesHistoryUci, options = {}, onBestMove, onEval) {
      if (!this.worker) {
        return false;
      }

      if (!this.isReady) {
        // Queue search so it starts immediately as soon as worker is ready
        this.pendingSearch = { movesHistoryUci, options, onBestMove, onEval };
        return true;
      }

      // If a search is already active, stop it cleanly first
      if (this.isSearching) {
        this.pendingSearch = { movesHistoryUci, options, onBestMove, onEval };
        this.stop();
        return true;
      }

      this.isSearching = true;
      this.onBestMove = onBestMove;
      this.onEvalUpdate = onEval;

      const skill = options.skillLevel ?? 10;
      const elo = options.elo ?? 1250;
      // In JS asm.js, depth 7-12 produces deep tactical master calculations within 1-2.5s
      const depth = Math.min(12, options.depth ?? 8);
      const movetime = options.movetime ?? 1200;

      this.configureStrength(skill, elo);

      const movesStr = (movesHistoryUci && movesHistoryUci.trim().length > 0)
        ? (' moves ' + movesHistoryUci.trim())
        : '';
      this.worker.postMessage('position startpos' + movesStr);
      this.worker.postMessage(`go depth ${depth} movetime ${movetime}`);
      return true;
    }

    stop() {
      if (this.worker) {
        this.worker.postMessage('stop');
      }
    }

    onMessage(line) {
      if (typeof line !== 'string') return;
      const trimmed = line.trim();

      if (trimmed.includes('readyok') || trimmed.includes('uciok')) {
        this.isReady = true;
        if (this.pendingSearch) {
          const p = this.pendingSearch;
          this.pendingSearch = null;
          this.analyzePosition(p.movesHistoryUci, p.options, p.onBestMove, p.onEvalUpdate);
        }
        return;
      }

      // Parse Live Evaluation Score for Eval Bar:
      if (trimmed.includes('score cp') || trimmed.includes('score mate')) {
        const cpMatch = trimmed.match(/score cp (-?\d+)/);
        const mateMatch = trimmed.match(/score mate (-?\d+)/);
        const pvMatch = trimmed.match(/pv (\w{4})/);

        let evalScore = 0;
        let evalDisplay = '0.0';

        if (mateMatch) {
          const m = parseInt(mateMatch[1], 10);
          evalScore = m > 0 ? 10000 : -10000;
          evalDisplay = (m > 0 ? '+M' : '-M') + Math.abs(m);
        } else if (cpMatch) {
          const cp = parseInt(cpMatch[1], 10);
          evalScore = cp;
          evalDisplay = (cp > 0 ? '+' : '') + (cp / 100).toFixed(1);
        }

        if (typeof this.onEvalUpdate === 'function') {
          this.onEvalUpdate({
            score: evalScore,
            display: evalDisplay,
            bestMoveUci: pvMatch ? pvMatch[1] : null
          });
        }
      }

      // Parse Best Move with whitespace-resilient match
      if (trimmed.startsWith('bestmove')) {
        this.isSearching = false;
        const parts = trimmed.split(/\s+/);
        const bestMoveUci = parts[1];

        const cb = this.onBestMove;
        this.onBestMove = null;

        if (typeof cb === 'function' && bestMoveUci && bestMoveUci !== '(none)') {
          cb(bestMoveUci);
        }

        if (this.pendingSearch) {
          const p = this.pendingSearch;
          this.pendingSearch = null;
          this.analyzePosition(p.movesHistoryUci, p.options, p.onBestMove, p.onEvalUpdate);
        }
      }
    }
  }

  // ---------------------------------------------------------------------------
  // PESTO POSITIONAL PIECE-SQUARE TABLES (FALLBACK & QUICK EVAL)
  // ---------------------------------------------------------------------------
  const PIECE_VALS = { p: 100, n: 320, b: 330, r: 500, q: 900, k: 20000 };

  const PST_PAWN = [
      0,   0,   0,   0,   0,   0,   0,   0,
     50,  50,  50,  50,  50,  50,  50,  50,
     10,  10,  20,  30,  30,  20,  10,  10,
      5,   5,  10,  25,  25,  10,   5,   5,
      0,   0,   0,  20,  20,   0,   0,   0,
      5,  -5, -10,   0,   0, -10,  -5,   5,
      5,  10,  10, -20, -20,  10,  10,   5,
      0,   0,   0,   0,   0,   0,   0,   0
  ];

  const PST_KNIGHT = [
    -50, -40, -30, -30, -30, -30, -40, -50,
    -40, -20,   0,   0,   0,   0, -20, -40,
    -30,   0,  10,  15,  15,  10,   0, -30,
    -30,   5,  15,  20,  20,  15,   5, -30,
    -30,   0,  15,  20,  20,  15,   0, -30,
    -30,   5,  10,  15,  15,  10,   5, -30,
    -40, -20,   0,   5,   5,   0, -20, -40,
    -50, -40, -30, -30, -30, -30, -40, -50
  ];

  const PST_BISHOP = [
    -20, -10, -10, -10, -10, -10, -10, -20,
    -10,   0,   0,   0,   0,   0,   0, -10,
    -10,   0,   5,  10,  10,   5,   0, -10,
    -10,   5,   5,  10,  10,   5,   5, -10,
    -10,   0,  10,  10,  10,  10,   0, -10,
    -10,  10,  10,  10,  10,  10,  10, -10,
    -10,   5,   0,   0,   0,   0,   5, -10,
    -20, -10, -10, -10, -10, -10, -10, -20
  ];

  const PST_ROOK = [
      0,   0,   0,   0,   0,   0,   0,   0,
      5,  10,  10,  10,  10,  10,  10,   5,
     -5,   0,   0,   0,   0,   0,   0,  -5,
     -5,   0,   0,   0,   0,   0,   0,  -5,
     -5,   0,   0,   0,   0,   0,   0,  -5,
     -5,   0,   0,   0,   0,   0,   0,  -5,
     -5,   0,   0,   0,   0,   0,   0,  -5,
      0,   0,   0,   5,   5,   0,   0,   0
  ];

  const PST_QUEEN = [
    -20, -10, -10,  -5,  -5, -10, -10, -20,
    -10,   0,   0,   0,   0,   0,   0, -10,
    -10,   0,   5,   5,   5,   5,   0, -10,
     -5,   0,   5,   5,   5,   5,   0,  -5,
      0,   0,   5,   5,   5,   5,   0,  -5,
    -10,   5,   5,   5,   5,   5,   0, -10,
    -10,   0,   5,   0,   0,   0,   0, -10,
    -20, -10, -10,  -5,  -5, -10, -10, -20
  ];

  const PST_KING = [
    -30, -40, -40, -50, -50, -40, -40, -30,
    -30, -40, -40, -50, -50, -40, -40, -30,
    -30, -40, -40, -50, -50, -40, -40, -30,
    -30, -40, -40, -50, -50, -40, -40, -30,
    -20, -30, -30, -40, -40, -30, -30, -20,
    -10, -20, -20, -20, -20, -20, -20, -10,
     20,  20,   0,   0,   0,   0,  20,  20,
     20,  30,  10,   0,   0,  10,  30,  20
  ];

  const PST_MAP = { p: PST_PAWN, n: PST_KNIGHT, b: PST_BISHOP, r: PST_ROOK, q: PST_QUEEN, k: PST_KING };

  const THEME_SQUARE_COLORS = {
    neo_emerald: {
      light: '#f0fdf4',
      dark: '#16a34a',
      lightCoord: '#15803d',
      darkCoord: '#bbf7d0'
    },
    royal_wood: {
      light: '#fef3c7',
      dark: '#b45309',
      lightCoord: '#78350f',
      darkCoord: '#fde68a'
    },
    cyber_neon: {
      light: '#0f172a',
      dark: '#0284c7',
      lightCoord: '#06b6d4',
      darkCoord: '#38bdf8'
    },
    gothic_crimson: {
      light: '#f5f5f4',
      dark: '#991b1b',
      lightCoord: '#7f1d1d',
      darkCoord: '#fecaca'
    },
    ocean_blue: {
      light: '#e0f2fe',
      dark: '#0369a1',
      lightCoord: '#075985',
      darkCoord: '#bae6fd'
    }
  };

  const OPENING_BOOK = {
    // 1st move (White)
    "": ["e2e4", "d2d4", "c2c4", "g1f3"],

    // Responses to 1. e4
    "e2e4": ["e7e5", "c7c5", "e7e6", "c7c6", "d7d6", "d7d5", "g7g6", "g8f6"],

    // 1. e4 e5 (Open Game)
    "e2e4 e7e5": ["g1f3", "f1c4", "d2d4", "b1c3", "f2f4"],
    "e2e4 e7e5 g1f3": ["b8c6", "g8f6", "d7d6"],
    "e2e4 e7e5 g1f3 b8c6": ["f1c4", "f1b5", "d2d4", "b1c3"],
    // Italian Game
    "e2e4 e7e5 g1f3 b8c6 f1c4": ["f8c5", "g8f6", "d7d6"],
    "e2e4 e7e5 g1f3 b8c6 f1c4 f8c5": ["c2c3", "d2d3", "b1c3", "e1g1"],
    // Ruy Lopez
    "e2e4 e7e5 g1f3 b8c6 f1b5": ["a7a6", "g8f6", "d7d6", "f8c5"],
    "e2e4 e7e5 g1f3 b8c6 f1b5 a7a6": ["b5a4", "b5c6"],
    // Scotch Game
    "e2e4 e7e5 g1f3 b8c6 d2d4": ["e5d4"],
    "e2e4 e7e5 g1f3 b8c6 d2d4 e5d4": ["f3d4"],

    // 1. e4 c5 (Sicilian Defense)
    "e2e4 c7c5": ["g1f3", "b1c3", "c2c3", "d2d4"],
    "e2e4 c7c5 g1f3": ["d7d6", "e7e6", "b8c6", "g7g6", "a7a6"],
    "e2e4 c7c5 g1f3 d7d6": ["d2d4", "f1b5", "c2c3"],
    "e2e4 c7c5 g1f3 d7d6 d2d4": ["c5d4"],
    "e2e4 c7c5 g1f3 d7d6 d2d4 c5d4": ["f3d4"],
    "e2e4 c7c5 g1f3 e7e6": ["d2d4", "c2c3", "b1c3"],
    "e2e4 c7c5 c2c3": ["d7d5", "g8f6", "e7e6"],

    // 1. e4 e6 (French Defense)
    "e2e4 e7e6": ["d2d4", "d2d3", "c2c4"],
    "e2e4 e7e6 d2d4": ["d7d5"],
    "e2e4 e7e6 d2d4 d7d5": ["b1c3", "b1d2", "e4e5", "e4d5"],

    // 1. e4 c6 (Caro-Kann Defense)
    "e2e4 c7c6": ["d2d4", "b1c3", "g1f3"],
    "e2e4 c7c6 d2d4": ["d7d5"],
    "e2e4 c7c6 d2d4 d7d5": ["b1c3", "e4e5", "e4d5", "b1d2"],

    // 1. e4 d7d5 (Scandinavian)
    "e2e4 d7d5": ["e4d5"],
    "e2e4 d7d5 e4d5": ["d8d5", "g8f6"],

    // Responses to 1. d4
    "d2d4": ["d7d5", "g8f6", "e7e6", "f7f5", "c7c5"],
    // 1. d4 d5 (Queen's Pawn Game)
    "d2d4 d7d5": ["c2c4", "g1f3", "c1f4", "b1c3", "e2e3"],
    "d2d4 d7d5 c2c4": ["e7e6", "c7c6", "d5c4", "g8f6"],
    "d2d4 d7d5 c2c4 e7e6": ["b1c3", "g1f3", "c4d5"],
    "d2d4 d7d5 c2c4 c7c6": ["g1f3", "b1c3", "c4d5"],
    // London System
    "d2d4 d7d5 c1f4": ["g8f6", "c7c5", "e7e6"],

    // 1. d4 Nf6 (Indian Defenses)
    "d2d4 g8f6": ["c2c4", "g1f3", "c1g5", "c1f4"],
    "d2d4 g8f6 c2c4": ["g7g6", "e7e6", "c7c5"],
    // King's Indian
    "d2d4 g8f6 c2c4 g7g6": ["b1c3", "g1f3", "g2g3"],
    // Nimzo / Queen's Indian
    "d2d4 g8f6 c2c4 e7e6": ["b1c3", "g1f3", "g2g3"],

    // 1. c4 (English Opening)
    "c2c4": ["e7e5", "c7c5", "g8f6", "e7e6"],
    "c2c4 e7e5": ["b1c3", "g1f3", "g2g3"],

    // 1. Nf3 (Reti Opening)
    "g1f3": ["d7d5", "g8f6", "c7c5"],
    "g1f3 d7d5": ["d2d4", "c2c4", "g2g3"]
  };

  function evaluateBoard(g, botColor) {
    let score = 0;
    const b = g.board();
    for (let r = 0; r < 8; r++) {
      for (let f = 0; f < 8; f++) {
        const piece = b[r][f];
        if (!piece) continue;
        const isWhite = (piece.color === 'w');
        const val = PIECE_VALS[piece.type] || 0;
        const pst = PST_MAP[piece.type];
        const pstIdx = isWhite ? (r * 8 + f) : ((7 - r) * 8 + f);
        const posVal = pst ? pst[pstIdx] : 0;
        const total = val + posVal;
        score += isWhite ? total : -total;
      }
    }
    return (botColor === 'w') ? score : -score;
  }

  function orderMoves(moves) {
    return moves.sort((a, b) => {
      let sa = 0, sb = 0;
      if (a.captured) sa += 10000 + (PIECE_VALS[a.captured] || 100) * 10 - (PIECE_VALS[a.piece] || 100);
      if (b.captured) sb += 10000 + (PIECE_VALS[b.captured] || 100) * 10 - (PIECE_VALS[b.piece] || 100);
      if (a.promotion) sa += 9000;
      if (b.promotion) sb += 9000;
      if (a.san && a.san.includes('+')) sa += 500;
      if (b.san && b.san.includes('+')) sb += 500;
      return sb - sa;
    });
  }

  function quiescence(g, alpha, beta, isMax, botColor, qDepth) {
    const standPat = evaluateBoard(g, botColor);
    if (qDepth <= 0) return standPat;

    if (isMax) {
      if (standPat >= beta) return beta;
      if (standPat > alpha) alpha = standPat;
      const captures = orderMoves(g.moves({ verbose: true }).filter(m => m.captured));
      for (const m of captures) {
        g.move(m);
        const score = quiescence(g, alpha, beta, false, botColor, qDepth - 1);
        g.undo();
        if (score >= beta) return beta;
        if (score > alpha) alpha = score;
      }
      return alpha;
    } else {
      if (standPat <= alpha) return alpha;
      if (standPat < beta) beta = standPat;
      const captures = orderMoves(g.moves({ verbose: true }).filter(m => m.captured));
      for (const m of captures) {
        g.move(m);
        const score = quiescence(g, alpha, beta, true, botColor, qDepth - 1);
        g.undo();
        if (score <= alpha) return alpha;
        if (score < beta) beta = score;
      }
      return beta;
    }
  }

  function minimax(g, depth, alpha, beta, isMax, botColor, maxDepth) {
    if (depth === 0) return quiescence(g, alpha, beta, isMax, botColor, 4);

    const moves = orderMoves(g.moves({ verbose: true }));
    if (!moves.length) {
      if (g.in_check()) return isMax ? (-99999 + (maxDepth - depth)) : (99999 - (maxDepth - depth));
      return 0;
    }

    if (isMax) {
      let maxEval = -Infinity;
      for (const m of moves) {
        g.move(m);
        const score = minimax(g, depth - 1, alpha, beta, false, botColor, maxDepth);
        g.undo();
        maxEval = Math.max(maxEval, score);
        alpha = Math.max(alpha, score);
        if (beta <= alpha) break;
      }
      return maxEval;
    } else {
      let minEval = Infinity;
      for (const m of moves) {
        g.move(m);
        const score = minimax(g, depth - 1, alpha, beta, true, botColor, maxDepth);
        g.undo();
        minEval = Math.min(minEval, score);
        beta = Math.min(beta, score);
        if (beta <= alpha) break;
      }
      return minEval;
    }
  }

  // ---------------------------------------------------------------------------
  // RisingRooksBoard Controller
  // ---------------------------------------------------------------------------
  class RisingRooksBoard {
    constructor(containerId, options = {}) {
      this.container = document.getElementById(containerId);
      if (!this.container) throw new Error('Board container not found: ' + containerId);

      this.options = Object.assign({
        orientation: 'white',
        interactive: true,
        gameId: null,
        gameType: 'bot',
        botLevel: 3, // 1=100, 3=750, 5=1250, 8=2000, 10=2700
        botRating: 750,
        pieceTheme: 'neo_emerald', // neo_emerald, royal_wood, cyber_neon, gothic_crimson, ocean_blue
        evalBarId: null,
        evalBadgeId: null,
        onMove: null,
        onGameOver: null,
      }, options);

      const ChessConstructor = (typeof window.Chess === 'function')
        ? window.Chess
        : (window.Chess && typeof window.Chess.Chess === 'function' ? window.Chess.Chess : null);

      if (!ChessConstructor) {
        throw new Error('Chess.js library not loaded');
      }

      this.game = new ChessConstructor();
      this.selectedSquare = null;
      this.legalMovesForSelected = [];
      this.lastMove = null;
      this.hintMove = null;

      try {
        const savedTheme = localStorage.getItem('rising_rooks_board_theme');
        if (savedTheme && PIECE_THEMES[savedTheme]) {
          this.options.pieceTheme = savedTheme;
        }
      } catch (e) {}

      // Stockfish Bridge
      this.stockfish = new StockfishBridge();

      this.initDom();
      this.applyTheme(this.options.pieceTheme);
      this.render();
      this.triggerLiveEvaluation();

      // If playing as Black against a Bot, Bot is White and moves first!
      if (this.options.orientation === 'black' && this.options.gameType === 'bot' && this.game.turn() === 'w') {
        setTimeout(() => this.makeBotMove(), 1200);
      }
    }

    initDom() {
      this.container.innerHTML = `
        <div class="chess-board-grid" id="${this.container.id}-grid"></div>
      `;
      this.gridEl = document.getElementById(`${this.container.id}-grid`);
    }

    applyTheme(theme) {
      this.options.pieceTheme = theme;
      try {
        localStorage.setItem('rising_rooks_board_theme', theme);
      } catch (e) {}
      // Apply theme class to board stage wrapper
      const stage = this.container.closest('.board-stage') || document.querySelector('.board-stage');
      if (stage) {
        stage.className = 'board-stage board-theme-' + theme;
      }
    }

    setPieceTheme(theme) {
      this.applyTheme(theme);
      this.render();
    }

    showHint() {
      if (this.currentBestMoveUci && this.currentBestMoveUci.length >= 4) {
        this.hintMove = {
          from: this.currentBestMoveUci.substring(0, 2),
          to: this.currentBestMoveUci.substring(2, 4)
        };
        this.render();
        setTimeout(() => {
          this.hintMove = null;
          this.render();
        }, 2500);
      }
    }

    render() {
      this.gridEl.innerHTML = '';
      const isFlipped = (this.options.orientation === 'black');
      const themeColors = THEME_SQUARE_COLORS[this.options.pieceTheme] || THEME_SQUARE_COLORS.neo_emerald;

      const ranks = isFlipped ? [1, 2, 3, 4, 5, 6, 7, 8] : [8, 7, 6, 5, 4, 3, 2, 1];
      const files = isFlipped ? ['h', 'g', 'f', 'e', 'd', 'c', 'b', 'a'] : ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];

      for (let r = 0; r < 8; r++) {
        for (let f = 0; f < 8; f++) {
          const rank = ranks[r];
          const file = files[f];
          const sq = file + rank;
          const isLight = ((r + f) % 2 === 0);

          const squareDiv = document.createElement('div');
          squareDiv.className = `chess-square ${isLight ? 'light' : 'dark'}`;
          squareDiv.dataset.square = sq;
          squareDiv.style.setProperty('background-color', isLight ? themeColors.light : themeColors.dark, 'important');

          // Rank label
          if (f === 0) {
            const rLabel = document.createElement('span');
            rLabel.className = 'coord-rank';
            rLabel.textContent = rank;
            rLabel.style.color = isLight ? themeColors.lightCoord : themeColors.darkCoord;
            squareDiv.appendChild(rLabel);
          }
          // File label
          if (r === 7) {
            const fLabel = document.createElement('span');
            fLabel.className = 'coord-file';
            fLabel.textContent = file;
            fLabel.style.color = isLight ? themeColors.lightCoord : themeColors.darkCoord;
            squareDiv.appendChild(fLabel);
          }

          // Piece
          const piece = this.game.get(sq);
          if (piece) {
            const pieceChar = piece.color === 'w' ? piece.type.toUpperCase() : piece.type.toLowerCase();
            const pieceDiv = document.createElement('div');
            pieceDiv.className = `chess-piece piece-theme-${this.options.pieceTheme}`;
            pieceDiv.innerHTML = getPieceSvg(pieceChar, this.options.pieceTheme);
            squareDiv.appendChild(pieceDiv);
          }

          // Highlights
          if (this.selectedSquare === sq) {
            squareDiv.classList.add('selected');
          }
          if (this.lastMove && (this.lastMove.from === sq || this.lastMove.to === sq)) {
            squareDiv.classList.add('last-move');
          }
          if (this.hintMove) {
            if (this.hintMove.from === sq) squareDiv.classList.add('hint-from');
            if (this.hintMove.to === sq) squareDiv.classList.add('hint-to');
          }
          if (this.legalMovesForSelected.some(m => m.to === sq)) {
            const hasOpponent = piece && (piece.color !== this.game.turn());
            squareDiv.classList.add(hasOpponent ? 'legal-capture' : 'legal-move');
          }

          if (this.game.in_check() && piece && piece.type === 'k' && piece.color === this.game.turn()) {
            squareDiv.classList.add('in-check');
          }

          if (this.options.interactive) {
            squareDiv.addEventListener('click', () => this.handleSquareClick(sq));
          }

          this.gridEl.appendChild(squareDiv);
        }
      }
    }

    handleSquareClick(sq) {
      if (this.game.game_over()) return;

      const myColor = (this.options.orientation === 'black') ? 'b' : 'w';
      if (this.options.gameType === 'bot' && this.game.turn() !== myColor) {
        return;
      }

      if (this.selectedSquare === sq) {
        this.clearSelection();
        this.render();
        return;
      }

      const targetMove = this.legalMovesForSelected.find(m => m.to === sq);
      if (targetMove) {
        this.makeMove(targetMove);
        return;
      }

      const piece = this.game.get(sq);
      if (piece && piece.color === this.game.turn()) {
        this.selectedSquare = sq;
        this.legalMovesForSelected = this.game.moves({ square: sq, verbose: true });
        this.render();
      } else {
        this.clearSelection();
        this.render();
      }
    }

    clearSelection() {
      this.selectedSquare = null;
      this.legalMovesForSelected = [];
    }

    makeMove(moveObj) {
      this.isBotThinking = false;
      if (typeof moveObj === 'object' && moveObj.flags && moveObj.flags.includes('p') && !moveObj.promotion) {
        moveObj.promotion = 'q';
      }
      const isCapture = typeof moveObj === 'object' && moveObj.captured;
      const move = this.game.move(moveObj);
      if (!move) return;

      // Play audio feedback
      if (this.game.in_check()) {
        ChessAudio.playCheck();
      } else if (isCapture || move.captured) {
        ChessAudio.playCapture();
      } else {
        ChessAudio.playMove();
      }

      this.lastMove = { from: move.from, to: move.to };
      this.clearSelection();
      this.render();

      if (typeof this.options.onMove === 'function') {
        this.options.onMove(move, this.game);
      }

      this.checkGameStatus();

      // Bot Turn Trigger
      const myColor = (this.options.orientation === 'black') ? 'b' : 'w';
      if (!this.game.game_over() && this.options.gameType === 'bot' && this.game.turn() !== myColor) {
        // When it is Bot's turn, makeBotMove handles calculating the move and streaming live eval together
        setTimeout(() => this.makeBotMove(), 250);
      } else {
        // When it is human's turn, evaluate current position for the student
        this.triggerLiveEvaluation();
      }
    }

    triggerLiveEvaluation() {
      const historyUci = this.game.history({ verbose: true }).map(m => m.from + m.to).join(' ');

      // Stream evaluation to update live Eval Bar
      this.stockfish.analyzePosition(historyUci, { skillLevel: 12, depth: 8, movetime: 300 }, null, (evalData) => {
        this.currentBestMoveUci = evalData.bestMoveUci;
        this.updateEvalBarUI(evalData.score, evalData.display);
      });
    }

    updateEvalBarUI(score, display) {
      const fillEl = document.getElementById(this.options.evalBarId || 'evalBarFill');
      const badgeEl = document.getElementById(this.options.evalBadgeId || 'evalBarBadge');

      if (badgeEl) badgeEl.textContent = display;

      if (fillEl) {
        // Clamp height between 5% and 95%
        let pct = 50 + (score / 15);
        pct = Math.max(6, Math.min(94, pct));
        fillEl.style.height = pct + '%';
      }
    }

    makeBotMove() {
      if (this.game.game_over()) return;
      if (this.isBotThinking) return;
      this.isBotThinking = true;

      const moves = this.game.moves({ verbose: true });
      if (!moves.length) {
        this.isBotThinking = false;
        return;
      }

      const botThinkStartTime = Date.now();
      // Realistic calculation pause (800ms to 2400ms) so bot never moves in 0.1s
      const minThinkDelay = 800 + Math.floor(Math.random() * 1400);

      const botLvl = this.options.botLevel || 3;
      const botRating = this.options.botRating || 750;

      // 1. Level 1 (Rising Pawn - 100 Rating): Beginner / Child Level
      // 60% chance to make an uncalculated beginner move or blunder a piece.
      // Allows young learners to win, gain confidence, and enjoy chess!
      if (botLvl === 1) {
        if (Math.random() < 0.60) {
          const blunderMove = moves[Math.floor(Math.random() * moves.length)];
          const elapsed = Date.now() - botThinkStartTime;
          const delay = Math.max(0, minThinkDelay - elapsed);
          setTimeout(() => this.makeMove(blunderMove), delay);
          return;
        }
      }

      // 2. Level 2-3 (Rising Knight - 500-750 Rating): Casual Novice
      // 30% chance to make an uncalculated novice move / miss tactics.
      if (botLvl <= 3 && Math.random() < 0.30) {
        const casualMove = moves[Math.floor(Math.random() * moves.length)];
        const elapsed = Date.now() - botThinkStartTime;
        const delay = Math.max(0, minThinkDelay - elapsed);
        setTimeout(() => this.makeMove(casualMove), delay);
        return;
      }

      // 3. Opening Book: Strictly reserved for Master & GM bots (Level 6+)
      // Beginners and Novices must NOT play grandmaster theory!
      if (botLvl >= 6) {
        const bookList = OPENING_BOOK[historyUci];
        if (bookList && bookList.length) {
          const candidateUci = bookList[Math.floor(Math.random() * bookList.length)];
          const bookMove = moves.find(m => (m.from + m.to) === candidateUci);
          if (bookMove) {
            const elapsed = Date.now() - botThinkStartTime;
            const delay = Math.max(0, minThinkDelay - elapsed);
            setTimeout(() => this.makeMove(bookMove), delay);
            return;
          }
        }
      }

      // Calibrated competitive Stockfish profiles:
      // Rising Pawn (100): Skill 0, depth 2, movetime 350
      // Rising Knight (750): Skill 3, depth 3, movetime 450
      // Rising Bishop (1250): Skill 8, depth 6, movetime 700
      // Rising Rook (2000): Skill 18, depth 10, movetime 1400 (Master)
      // Rising Queen (2700): Skill 20 (MAX), depth 12, movetime 2000 (Grandmaster)
      const botProfiles = {
        1:  { skill: 0,  elo: 200,  depth: 2,  movetime: 350 },
        2:  { skill: 2,  elo: 450,  depth: 3,  movetime: 400 },
        3:  { skill: 4,  elo: 750,  depth: 4,  movetime: 500 },
        4:  { skill: 6,  elo: 1000, depth: 5,  movetime: 650 },
        5:  { skill: 8,  elo: 1250, depth: 6,  movetime: 800 },
        6:  { skill: 12, elo: 1500, depth: 8,  movetime: 1000 },
        7:  { skill: 15, elo: 1750, depth: 9,  movetime: 1200 },
        8:  { skill: 18, elo: 2000, depth: 10, movetime: 1400 },
        9:  { skill: 19, elo: 2400, depth: 11, movetime: 1700 },
        10: { skill: 20, elo: 2700, depth: 12, movetime: 2000 },
      };

      const cfg = botProfiles[botLvl] || { skill: 8, elo: botRating, depth: 6, movetime: 700 };

      // Fallback timer safeguard: if Web Worker doesn't respond in time, use strong minimax
      let moveExecuted = false;
      const fallbackTimer = setTimeout(() => {
        if (!moveExecuted) {
          moveExecuted = true;
          this.fallbackMinimaxMove(botThinkStartTime, minThinkDelay);
        }
      }, cfg.movetime + 3000);

      const workerSuccess = this.stockfish.analyzePosition(
        historyUci,
        { skillLevel: cfg.skill, elo: cfg.elo, depth: cfg.depth, movetime: cfg.movetime },
        (bestMoveUci) => {
          if (moveExecuted) return;
          moveExecuted = true;
          clearTimeout(fallbackTimer);

          const from = bestMoveUci.substring(0, 2);
          const to = bestMoveUci.substring(2, 4);
          const promo = bestMoveUci.length > 4 ? bestMoveUci[4] : 'q';
          let matchMove = moves.find(m => m.from === from && m.to === to);

          // For beginner bots (Level 1-3), occasional human blunder injection
          if (botLvl === 1 && Math.random() < 0.45) {
            matchMove = moves[Math.floor(Math.random() * moves.length)];
          } else if (botLvl <= 3 && Math.random() < 0.20) {
            matchMove = moves[Math.floor(Math.random() * moves.length)];
          }

          const elapsed = Date.now() - botThinkStartTime;
          const delay = Math.max(0, minThinkDelay - elapsed);

          setTimeout(() => {
            if (matchMove) {
              this.makeMove(matchMove);
            } else {
              this.fallbackMinimaxMove(botThinkStartTime, 0);
            }
          }, delay);
        },
        (evalData) => {
          this.currentBestMoveUci = evalData.bestMoveUci;
          this.updateEvalBarUI(evalData.score, evalData.display);
        }
      );

      if (!workerSuccess) {
        clearTimeout(fallbackTimer);
        this.fallbackMinimaxMove(botThinkStartTime, minThinkDelay);
      }
    }

    fallbackMinimaxMove(botThinkStartTime = 0, minThinkDelay = 0) {
      const moves = this.game.moves({ verbose: true });
      if (!moves.length) {
        this.isBotThinking = false;
        return;
      }

      const botColor = this.game.turn();
      const botLvl = this.options.botLevel || 3;

      // Competitive depth with 4-ply quiescence
      const searchDepth = (botLvl >= 8) ? 4 : (botLvl >= 4 ? 3 : 2);
      const sorted = orderMoves(moves);

      let best = sorted[0];
      let maxVal = -Infinity;

      // Level 1 (Rising Pawn 100) and Level 2-3 (Rising Knight 750) blunders
      if (botLvl === 1 && Math.random() < 0.60) {
        best = moves[Math.floor(Math.random() * moves.length)];
      } else if (botLvl <= 3 && Math.random() < 0.30) {
        best = moves[Math.floor(Math.random() * moves.length)];
      } else {
        for (const m of sorted) {
          this.game.move(m);
          const s = minimax(this.game, searchDepth - 1, -Infinity, Infinity, false, botColor, searchDepth);
          this.game.undo();
          if (s > maxVal) {
            maxVal = s;
            best = m;
          }
        }
      }

      const elapsed = Date.now() - (botThinkStartTime || Date.now());
      const delay = Math.max(0, (minThinkDelay || 0) - elapsed);
      setTimeout(() => {
        this.makeMove(best);
      }, delay);
    }

    checkGameStatus() {
      if (this.game.in_checkmate()) {
        const winner = this.game.turn() === 'w' ? 'Black' : 'White';
        const result = this.game.turn() === 'w' ? '0-1' : '1-0';
        this.triggerGameOver(result, `${winner} wins by Checkmate!`);
      } else if (this.game.in_draw() || this.game.in_stalemate() || this.game.in_threefold_repetition()) {
        this.triggerGameOver('1/2-1/2', 'Game drawn by stalemate / repetition.');
      }
    }

    triggerGameOver(result, reason) {
      if (typeof this.options.onGameOver === 'function') {
        this.options.onGameOver(result, reason, this.game);
      }
    }

    setPosition(fen) {
      this.game.load(fen);
      this.clearSelection();
      this.lastMove = null;
      this.render();
      this.triggerLiveEvaluation();
    }

    reset() {
      this.game.reset();
      this.clearSelection();
      this.lastMove = null;
      this.render();
      this.triggerLiveEvaluation();
    }
  }

  window.RisingRooksBoard = RisingRooksBoard;
})(window);
