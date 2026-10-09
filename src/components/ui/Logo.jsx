// Logo « Éclat » — badge avec dent stylisée + éclat (étincelle).
// S'adapte au fond : sombre par défaut, clair (onDark) sur surfaces foncées.
export default function Logo({ size = 32, onDark = false }) {
  const tile = onDark ? '#d1fc71' : '#0d1b15';
  const tooth = onDark ? '#0d1b15' : '#d1fc71';
  const spark = onDark ? '#0d1b15' : '#f2f2ef';
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 48 48"
      role="img"
      aria-hidden="true"
      focusable="false"
    >
      <rect width="48" height="48" rx="13" fill={tile} />
      <path
        fill={tooth}
        d="M24 11c-5.4 0-9.2 3-9.2 8 0 3.3 1.2 5.7 2.1 9 .8 2.9 1.1 6.6 3.1 6.6 1.8 0 1.9-3.6 2.6-5.7.4-1.2.7-2 1.4-2s1 .8 1.4 2c.7 2.1.8 5.7 2.6 5.7 2 0 2.3-3.7 3.1-6.6.9-3.3 2.1-5.7 2.1-9 0-5-3.8-8-9.2-8z"
      />
      <path
        fill={spark}
        d="M35 9.5l.9 2.4 2.4.9-2.4.9-.9 2.4-.9-2.4-2.4-.9 2.4-.9z"
      />
    </svg>
  );
}
