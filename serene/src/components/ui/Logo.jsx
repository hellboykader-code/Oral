// Logo « Sérène » — badge : dent stylisée + petite feuille (soin doux & naturel).
// S'adapte au fond : terracotta par défaut, clair (onDark) sur surfaces foncées.
export default function Logo({ size = 32, onDark = false }) {
  const tile = onDark ? '#faf7f5' : '#963f36';
  const tooth = onDark ? '#963f36' : '#faf7f5';
  const leaf = onDark ? '#6e8568' : '#dbf6e9';
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 48 48"
      role="img"
      aria-hidden="true"
      focusable="false"
    >
      <rect width="48" height="48" rx="14" fill={tile} />
      <path
        fill={tooth}
        d="M23 12c-5.2 0-8.9 2.9-8.9 7.7 0 3.2 1.1 5.5 2 8.7.8 2.8 1.1 6.4 3 6.4 1.7 0 1.8-3.5 2.5-5.5.4-1.1.6-1.9 1.3-1.9s.9.8 1.3 1.9c.7 2 .8 5.5 2.5 5.5 1.9 0 2.2-3.6 3-6.4.9-3.2 2-5.5 2-8.7 0-4.8-3.7-7.7-8.7-7.7z"
      />
      <path
        fill={leaf}
        d="M34.6 10.2c-3.1.5-5 2.6-4.7 5.3 2.7.2 4.9-1.6 4.7-5.3z"
      />
    </svg>
  );
}
