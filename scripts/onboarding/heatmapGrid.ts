// project-root/scripts/onboarding/heatmapGrid.ts

import type { OnboardingEvent } from '../types';
import { onAuditEvent, rhythmTick } from './auditHeatmap';

export type HeatmapPalette = 'warm' | 'cool' | 'mono' | ((intensity: number) => string);

export interface HeatmapOptions {
  containerId?: string;    // DOM container to mount into (defaults to "audit-heatmap")
  rows?: number;           // number of rows (defaults 6)
  cols?: number;           // number of cols (defaults 8; matches rhythmTick 0..7)
  cell?: number;           // cell size in px (defaults 14)
  gap?: number;            // gap between cells in px (defaults 3)
  decayMs?: number;        // ms for a pulse to fade out (defaults 2000)
  palette?: HeatmapPalette;// color palette or custom function
  ariaLabel?: string;      // accessibility label for the SVG
}

type Unsub = () => void;

const hasDOM = () => typeof document !== 'undefined';

const defaultColor = (intensity: number, mode: 'warm' | 'cool' | 'mono'): string => {
  const clamped = Math.max(0, Math.min(1, intensity));
  if (mode === 'mono') {
    const g = Math.round(245 - clamped * 120);
    return `rgb(${g}, ${g}, ${g})`;
  }
  if (mode === 'cool') {
    // teal-blue scale
    const h = 200; // base hue
    const s = 80;
    const l = Math.round(95 - clamped * 45);
    return `hsl(${h} ${s}% ${l}%)`;
  }
  // warm: yellow-orange scale
  const h = Math.round(40 - clamped * 20); // 40→20
  const s = 90;
  const l = Math.round(96 - clamped * 50); // 96→46
  return `hsl(${h} ${s}% ${l}%)`;
};

export const createHeatmapGrid = (opts: HeatmapOptions = {}) => {
  if (!hasDOM()) {
    return {
      mount() {},
      destroy() {},
      pulseCell(_row: number, _col: number) {},
      repaint() {},
      setPalette(_p: HeatmapPalette) {},
    };
  }

  // Options
  const containerId = opts.containerId ?? 'audit-heatmap';
  const rows = Math.max(1, Math.floor(opts.rows ?? 6));
  const cols = Math.max(1, Math.floor(opts.cols ?? 8)); // aligns with rhythmTick() 0..7
  const cell = Math.max(2, Math.floor(opts.cell ?? 14));
  const gap = Math.max(0, Math.floor(opts.gap ?? 3));
  const decayMs = Math.max(200, Math.floor(opts.decayMs ?? 2000));
  let palette: HeatmapPalette = opts.palette ?? 'warm';
  const ariaLabel = opts.ariaLabel ?? 'Onboarding audit heatmap';

  // DOM
  const container = document.getElementById(containerId);
  if (!container) {
    // eslint-disable-next-line no-console
    console.warn(`[heatmapGrid] container #${containerId} not found; call createHeatmapGrid after DOM is ready.`);
  }

  const svgNS = 'http://www.w3.org/2000/svg';
  const svg = document.createElementNS(svgNS, 'svg');
  svg.setAttribute('role', 'img');
  svg.setAttribute('aria-label', ariaLabel);

  const width = cols * cell + (cols - 1) * gap;
  const height = rows * cell + (rows - 1) * gap;
  svg.setAttribute('width', String(width));
  svg.setAttribute('height', String(height));
  svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
  svg.style.display = 'block';

  // State
  const intensity: number[][] = Array.from({ length: rows }, () => Array.from({ length: cols }, () => 0));
  const lastBump: number[][] = Array.from({ length: rows }, () => Array.from({ length: cols }, () => 0));

  // Cells
  const rects: SVGRectElement[][] = Array.from({ length: rows }, (_, r) => {
    return Array.from({ length: cols }, (_, c) => {
      const x = c * (cell + gap);
      const y = r * (cell + gap);
      const rect = document.createElementNS(svgNS, 'rect');
      rect.setAttribute('x', String(x));
      rect.setAttribute('y', String(y));
      rect.setAttribute('width', String(cell));
      rect.setAttribute('height', String(cell));
      rect.setAttribute('rx', String(Math.floor(Math.min(4, cell / 4))));
      rect.setAttribute('ry', String(Math.floor(Math.min(4, cell / 4))));
      rect.setAttribute('data-row', String(r));
      rect.setAttribute('data-col', String(c));
      rect.style.fill = colorFor(0);
      svg.appendChild(rect);
      return rect;
    });
  });

  function colorFor(i: number): string {
    if (typeof palette === 'function') return palette(Math.max(0, Math.min(1, i)));
    return defaultColor(i, palette);
  }

  function repaint(): void {
    for (let r = 0; r < rows; r++) {
      for (let c = 0; c < cols; c++) {
        rects[r][c].style.fill = colorFor(intensity[r][c]);
      }
    }
  }

  function decay(now: number): void {
    for (let r = 0; r < rows; r++) {
      for (let c = 0; c < cols; c++) {
        const last = lastBump[r][c];
        if (!last) continue;
        const elapsed = now - last;
        const remain = Math.max(0, 1 - elapsed / decayMs);
        if (remain !== intensity[r][c]) {
          intensity[r][c] = remain;
        }
      }
    }
  }

  function bump(row: number, col: number): void {
    if (row < 0 || row >= rows || col < 0 || col >= cols) return;
    intensity[row][col] = 1;
    lastBump[row][col] = Date.now();
  }

  // Map event → grid coordinates
  function locate(evt: OnboardingEvent): { row: number; col: number } {
    const col = (typeof evt.rhythmIndex === 'number' ? evt.rhythmIndex : rhythmTick()) % cols;

    // Priority rows by event type
    let row = 0;
    switch (evt.type) {
      case 'badge_awarded': row = 0; break;
      case 'overlay_shown': row = 1; break;
      case 'overlay_step': row = 2; break;
      case 'overlay_hidden': row = 3; break;
      default: row = 4; break;
    }
    // Spread steps across lower rows if available
    if (typeof evt.step === 'number') {
      row = Math.min(rows - 1, row + (evt.step % Math.max(1, rows - row)));
    }
    return { row, col };
  }

  // Animation loop
  let raf = 0;
  const tick = () => {
    const now = Date.now();
    decay(now);
    repaint();
    raf = requestAnimationFrame(tick);
  };

  // Subscription
  const unsub: Unsub = onAuditEvent((evt) => {
    const { row, col } = locate(evt);
    bump(row, col);
  });

  // Public API
  const mount = () => {
    if (container && !container.contains(svg)) {
      container.appendChild(svg);
    }
    if (!raf) raf = requestAnimationFrame(tick);
  };

  const destroy = () => {
    unsub();
    if (raf) cancelAnimationFrame(raf);
    raf = 0;
    if (svg.parentNode) svg.parentNode.removeChild(svg);
  };

  const pulseCell = (row: number, col: number) => bump(row, col);

  const setPalette = (p: HeatmapPalette) => { palette = p; repaint(); };

  // Auto-mount if container exists
  mount();

  return { mount, destroy, pulseCell, repaint, setPalette };
};
