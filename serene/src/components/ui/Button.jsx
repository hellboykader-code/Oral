import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { spring } from '../../lib/motion.js';
import './Button.css';

const MotionLink = motion(Link);

/**
 * Bouton d'action avec retour tactile (hover/tap) — gestuelle Framer Motion.
 * Rendu en <Link>, <a> ou <button> selon les props.
 */
export default function Button({
  children,
  to,
  href,
  variant = 'primary',
  size = 'md',
  className = '',
  ...rest
}) {
  const cls = `btn btn--${variant} btn--${size} ${className}`;
  const anim = {
    whileHover: { scale: 1.03 },
    whileTap: { scale: 0.97 },
    transition: spring,
  };

  if (to) {
    return (
      <MotionLink to={to} className={cls} {...anim} {...rest}>
        {children}
      </MotionLink>
    );
  }
  if (href) {
    return (
      <motion.a href={href} className={cls} {...anim} {...rest}>
        {children}
      </motion.a>
    );
  }
  return (
    <motion.button className={cls} {...anim} {...rest}>
      {children}
    </motion.button>
  );
}
