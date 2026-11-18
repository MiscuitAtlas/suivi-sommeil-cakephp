<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\NightsTable;
use App\Service\WeekSummary;
use Cake\Http\Response;
use Cake\I18n\Date;

/**
 * Nuits de l'utilisateur connecté : historique, saisie, modification et bilan de la semaine.
 */
class NightsController extends AppController
{
    protected NightsTable $Nights;

    /**
     * @var array<string, mixed>
     */
    protected array $paginate = [
        'limit' => 15,
        'order' => ['Nights.sleep_date' => 'DESC'],
        'sortableFields' => ['sleep_date', 'duration', 'cycles', 'morning_score'],
    ];

    /**
     * Configure la pagination des nuits.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Nights = $this->fetchTable('Nights');
    }

    /**
     * Historique des nuits de l'utilisateur connecté.
     *
     * @return void
     */
    public function index(): void
    {
        $query = $this->Authorization->applyScope($this->Nights->find());
        $this->set('nights', $this->paginate($query));
    }

    /**
     * Enregistre une nuit pour l'utilisateur connecté.
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $this->Authorization->skipAuthorization();
        $night = $this->Nights->newEmptyEntity();
        // « ?date=2026-11-16 » préremplit la date, depuis le bilan de la semaine.
        $requested = $this->dateFromQuery('date');
        $today = Date::today();
        $night->sleep_date = $requested !== null && $requested->lessThanOrEquals($today) ? $requested : $today;
        if ($this->request->is('post')) {
            $night = $this->Nights->patchEntity($night, $this->request->getData());
            // L'utilisateur est celui de la session, jamais une valeur du formulaire.
            $night->user_id = $this->currentUserId();
            if ($this->Nights->save($night)) {
                $this->Flash->success(sprintf(
                    'Nuit du %s enregistrée : %s cycles.',
                    $night->sleep_date->i18nFormat('d MMMM'),
                    $this->formatCycles($night->cyclesValue()),
                ));

                return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
            }
            $this->Flash->error('La nuit n\'a pas pu être enregistrée : corrigez les champs signalés.');
        }
        // Si la nuit de cette date existe déjà, la page propose de la modifier.
        $existing = $this->Nights->find('ownedBy', userId: $this->currentUserId())
            ->where(['sleep_date' => $night->sleep_date])
            ->first();
        $this->set(compact('night', 'existing'));

        return null;
    }

    /**
     * Modifie une nuit de l'utilisateur connecté.
     *
     * @param string $id
     * @return \Cake\Http\Response|null
     */
    public function edit(string $id): ?Response
    {
        $night = $this->Nights->get($id);
        $this->Authorization->authorize($night, 'edit');
        if ($this->request->is(['post', 'put', 'patch'])) {
            $night = $this->Nights->patchEntity($night, $this->request->getData());
            if ($this->Nights->save($night)) {
                $this->Flash->success('La nuit a été modifiée.');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('La nuit n\'a pas pu être modifiée : corrigez les champs signalés.');
        }
        $this->set(compact('night'));

        return null;
    }

    /**
     * Supprime une nuit de l'utilisateur connecté.
     *
     * @param string $id
     * @return \Cake\Http\Response
     */
    public function delete(string $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $night = $this->Nights->get($id);
        $this->Authorization->authorize($night, 'delete');
        if ($this->Nights->delete($night)) {
            $day = $night->sleep_date->i18nFormat('d MMMM yyyy');
            $this->Flash->success(sprintf('La nuit du %s a été supprimée.', $day));
        } else {
            $this->Flash->error('La nuit n\'a pas pu être supprimée.');
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Bilan d'une semaine, du lundi au dimanche. « ?du=2026-11-16 » choisit la semaine.
     */
    public function week(): void
    {
        $this->Authorization->skipAuthorization();
        $monday = WeekSummary::mondayOf($this->dateFromQuery('du') ?? Date::today());
        $nights = $this->Nights->find(
            'between',
            userId: $this->currentUserId(),
            from: $monday,
            to: $monday->addDays(6),
        );
        $summary = new WeekSummary($monday, $nights->all());

        $this->set([
            'summary' => $summary,
            'previous' => $monday->subDays(7),
            'next' => $monday->addDays(7)->lessThanOrEquals(Date::today()) ? $monday->addDays(7) : null,
            'isCurrent' => $monday->equals(WeekSummary::mondayOf(Date::today())),
        ]);
    }

    /**
     * Date « AAAA-MM-JJ » lue dans l'adresse, ou null si elle est absente ou invalide.
     */
    private function dateFromQuery(string $name): ?Date
    {
        $value = (string)$this->request->getQuery($name, '');
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) || !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) {
            return null;
        }

        return new Date($value);
    }

    /**
     * Nombre de cycles au format français (virgule décimale).
     *
     * @param float $cycles
     * @return string
     */
    private function formatCycles(float $cycles): string
    {
        return str_replace('.', ',', (string)$cycles);
    }
}
