import { useState } from 'react';
import { imageSrc, placeholder } from '../../data/images.js';

/**
 * Image responsive avec lazy-loading et repli automatique.
 * Si le fichier réel (public/images/<key>.webp) est absent, un placeholder
 * de marque (dégradé + libellé) s'affiche — le site reste soigné.
 */
export default function Img({
  imageKey,
  alt = '',
  variant = 0,
  className,
  eager = false,
  style,
}) {
  const real = imageSrc(imageKey);
  const fallback = placeholder(alt, variant);
  const [src, setSrc] = useState(real || fallback);

  return (
    <img
      src={src}
      alt={alt}
      className={className}
      style={style}
      loading={eager ? 'eager' : 'lazy'}
      decoding="async"
      onError={() => src !== fallback && setSrc(fallback)}
      draggable={false}
    />
  );
}
