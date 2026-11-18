<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\NightsTable;
use Cake\I18n\Date;
use Cake\TestSuite\TestCase;

class NightsTableTest extends TestCase
{
    protected array $fixtures = ['app.Users', 'app.Nights', 'app.Menus'];

    private NightsTable $Nights;

    protected function setUp(): void
    {
        parent::setUp();
        $this->Nights = $this->getTableLocator()->get('Nights');
    }

    private function data(array $overrides = []): array
    {
        return $overrides + [
            'sleep_date' => Date::today()->format('Y-m-d'),
            'bedtime' => '23:15',
            'wake_time' => '07:00',
            'morning_score' => '8',
            'sport' => '1',
        ];
    }

    public function testDurationAndCyclesAreComputed(): void
    {
        $night = $this->Nights->newEntity($this->data());
        $night->user_id = 1;
        $this->assertNotFalse($this->Nights->save($night));
        $saved = $this->Nights->get($night->id);
        $this->assertSame(465, $saved->duration);
        $this->assertEquals(5.2, (float)$saved->cycles);
    }

    public function testComputedFieldsCannotBeForged(): void
    {
        $night = $this->Nights->newEntity($this->data(['duration' => 9999, 'cycles' => 99, 'user_id' => 2]));
        $this->assertNull($night->user_id, 'user_id ne vient jamais du formulaire');
        $night->user_id = 1;
        $this->Nights->saveOrFail($night);
        $this->assertSame(465, $night->duration);
    }

    public function testRecomputedWhenTimesChange(): void
    {
        $night = $this->Nights->get(1);
        $this->Nights->patchEntity($night, ['wake_time' => '08:00']);
        $this->Nights->saveOrFail($night);
        $this->assertSame(540, $night->duration);
        $this->assertEquals(6.0, (float)$night->cycles);
    }

    public function testOneNightPerDay(): void
    {
        $night = $this->Nights->newEntity($this->data(['sleep_date' => Date::today()->subDays(1)->format('Y-m-d')]));
        $night->user_id = 1;
        $this->assertFalse($this->Nights->save($night));
        $this->assertContains('Une nuit est déjà enregistrée à cette date : modifiez-la plutôt.', $night->getError('sleep_date'));

        // Bruno peut avoir une nuit le même jour qu'Alice.
        $other = $this->Nights->newEntity($this->data(['sleep_date' => Date::today()->subDays(2)->format('Y-m-d')]));
        $other->user_id = 2;
        $this->assertNotFalse($this->Nights->save($other));
    }

    public function testValidation(): void
    {
        $cases = [
            'sleep_date' => ['sleep_date' => Date::today()->addDays(1)->format('Y-m-d')],
            'bedtime' => ['bedtime' => '25:00'],
            'morning_score' => ['morning_score' => '11'],
            'comment' => ['comment' => str_repeat('a', 1001)],
        ];
        foreach ($cases as $field => $data) {
            $night = $this->Nights->newEntity($this->data($data));
            $this->assertNotEmpty($night->getError($field), $field);
        }
    }

    public function testDurationMustBeRealistic(): void
    {
        foreach ([['23:00', '23:30'], ['06:00', '23:00']] as [$bed, $wake]) {
            $night = $this->Nights->newEntity($this->data(['bedtime' => $bed, 'wake_time' => $wake]));
            $night->user_id = 1;
            $this->assertFalse($this->Nights->save($night), "$bed → $wake");
            $this->assertNotEmpty($night->getError('wake_time'));
        }
    }

    public function testFindBetweenIsLimitedToTheUser(): void
    {
        $nights = $this->Nights->find('between', userId: 1, from: Date::today()->subDays(7), to: Date::today())->all();
        $this->assertCount(2, $nights);
        foreach ($nights as $night) {
            $this->assertSame(1, $night->user_id);
        }
    }

    public function testNightsAreDeletedWithTheirUser(): void
    {
        $users = $this->getTableLocator()->get('Users');
        $users->delete($users->get(1));
        $this->assertSame(0, $this->Nights->find()->where(['user_id' => 1])->count());
        $this->assertSame(1, $this->Nights->find()->where(['user_id' => 2])->count());
    }
}
