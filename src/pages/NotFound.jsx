import PageWrapper from '../components/ui/PageWrapper.jsx';
import Button from '../components/ui/Button.jsx';
import './NotFound.css';

export default function NotFound() {
  return (
    <PageWrapper title="Page introuvable — Cabinet dentaire">
      <section className="notfound">
        <div className="container notfound__inner">
          <span className="notfound__code">404</span>
          <h1>Oups ! Cette page a disparu.</h1>
          <p>
            La page que vous recherchez semble introuvable. Retournez à
            l'accueil ou prenez directement rendez-vous.
          </p>
          <div className="notfound__actions">
            <Button to="/" size="lg">Retour à l'accueil</Button>
            <Button to="/rendez-vous" variant="ghost" size="lg">Prendre rendez-vous</Button>
          </div>
        </div>
      </section>
    </PageWrapper>
  );
}
