<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\MenusTable;
use Cake\Http\Response;

/**
 * Gestion du menu de navigation, réservée aux administrateurs.
 */
class MenusController extends AppController
{
    protected MenusTable $Menus;

    /**
     * Les liens sont listés dans l'ordre d'affichage.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Menus = $this->fetchTable('Menus');
    }

    /**
     * Liste des liens du menu.
     *
     * @return void
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->Menus, 'index');
        $this->set('menus', $this->Menus->find()->orderBy(['position' => 'ASC'])->all());
        // Sur la démonstration en ligne, le menu est en lecture seule.
        $this->set('canChange', $this->Authorization->can($this->Menus, 'add'));
    }

    /**
     * Ajoute un lien en fin de menu.
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $this->Authorization->authorize($this->Menus, 'add');
        $menu = $this->Menus->newEmptyEntity();
        if ($this->request->is('post')) {
            $menu = $this->Menus->patchEntity($menu, $this->request->getData());
            if ($this->Menus->save($menu)) {
                $this->Flash->success('Le lien a été ajouté au menu.');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Le lien n\'a pas pu être ajouté.');
        }
        $this->set(compact('menu'));

        return null;
    }

    /**
     * Modifie un lien du menu.
     *
     * @param string $id
     * @return \Cake\Http\Response|null
     */
    public function edit(string $id): ?Response
    {
        $menu = $this->Menus->get($id);
        $this->Authorization->authorize($menu, 'edit');
        if ($this->request->is(['post', 'put', 'patch'])) {
            $menu = $this->Menus->patchEntity($menu, $this->request->getData());
            if ($this->Menus->save($menu)) {
                $this->Flash->success('Le lien a été modifié.');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Le lien n\'a pas pu être modifié.');
        }
        $this->set(compact('menu'));

        return null;
    }

    /**
     * Supprime un lien du menu.
     *
     * @param string $id
     * @return \Cake\Http\Response
     */
    public function delete(string $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $menu = $this->Menus->get($id);
        $this->Authorization->authorize($menu, 'delete');
        if ($this->Menus->delete($menu)) {
            $this->Flash->success(sprintf('Le lien « %s » a été supprimé.', $menu->label));
        } else {
            $this->Flash->error('Le lien n\'a pas pu être supprimé.');
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Monte ou descend un lien d'un cran.
     */
    public function move(string $id, string $direction): Response
    {
        $this->request->allowMethod(['post']);
        $menu = $this->Menus->get($id);
        $this->Authorization->authorize($menu, 'move');
        $this->Menus->move($menu, $direction === 'monter' ? 'up' : 'down');

        return $this->redirect(['action' => 'index', '#' => 'menu-' . $menu->id]);
    }
}
