// Logo « dent » vectoriel — hérite de la couleur courante.
export default function Logo({ size = 30 }) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 32 32"
      role="img"
      aria-hidden="true"
      focusable="false"
    >
      <rect width="32" height="32" rx="9" fill="currentColor" />
      <path
        d="M16 7c-3 0-5 1.6-5 4.5 0 2 .8 3.4 1.4 6.2.5 2.4.6 5.3 1.9 5.3 1 0 1-1.8 1.7-1.8s.7 1.8 1.7 1.8c1.3 0 1.4-2.9 1.9-5.3.6-2.8 1.4-4.2 1.4-6.2C21 8.6 19 7 16 7z"
        fill="#d1fc71"
      />
    </svg>
  );
}
