import Header from './Header.jsx';
import Footer from './Footer.jsx';

// Ossature commune : en-tête + contenu + pied de page.
export default function Layout({ children }) {
  return (
    <>
      <a className="skip-link" href="#main">
        Aller au contenu
      </a>
      <Header />
      <main id="main">{children}</main>
      <Footer />
    </>
  );
}
