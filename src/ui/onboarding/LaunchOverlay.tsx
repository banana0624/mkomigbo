// project-root/src/ui/onboarding/LaunchOverlay.tsx

import React from 'react';
import './Overlay.css';

type Props = {
  show: boolean;
  links?: { title: string; href: string }[];
};

export function LaunchOverlay({ show, links = [] }: Props) {
  if (!show) return null;
  return (
    <div className="overlay-root" role="dialog" aria-modal="true">
      <div className="overlay-card" style={{ animationDelay: '120ms' }}>
        <h2>🚀 We’re Live — Welcome to the Rhythm</h2>
        <p>Every contribution is a celebration. Start with these essentials:</p>
        <ul>
          {links.map((l) => (
            <li key={l.href}>
              <a href={l.href} target="_blank" rel="noreferrer">{l.title}</a>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
