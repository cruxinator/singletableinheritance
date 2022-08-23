<?php


namespace Tests\Packages\SingleTableInheritance;

use Cruxinator\SingleTableInheritance\Tests\Fixtures\User;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\WidgetNonSti;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\WidgetSti;
use Cruxinator\SingleTableInheritance\Tests\TestCase;

class TraitHasManyThroughTest extends TestCase
{
    public function testNonStiHasManyThroughDirectCount()
    {
        // Although this isn't strictly in scope of the STI package, we need a baseline to compare things to
        $user = new User();
        $user->name = 'Vehicle Owner';
        $user->save();

        // first level widgets
        $kid1 = new WidgetNonSti();
        $kid1->user()->associate($user);
        $this->assertTrue($kid1->save());

        $kid2 = new WidgetNonSti();
        $kid2->user()->associate($user);
        $this->assertTrue($kid2->save());

        $this->assertEquals(2, $user->plainWidgets()->count(), 'Expected two child widgets');

        // second level widgets
        $grandkid1 = new WidgetNonSti();
        $grandkid1->parentWidget()->associate($kid1);
        $this->assertTrue($grandkid1->save());

        $grandkid2 = new WidgetNonSti();
        $grandkid2->parentWidget()->associate($kid1);
        $this->assertTrue($grandkid2->save());

        $grandkid3 = new WidgetNonSti();
        $grandkid3->parentWidget()->associate($kid2);
        $this->assertTrue($grandkid3->save());

        $this->assertEquals(3, $user->kidPlainWidgets()->whereNotIn('widgets.id', [])->count(), 'Expected three grandchild widgets');
    }

    public function testNonStiHasManyThroughDirectGet()
    {
        // Although this isn't strictly in scope of the STI package, we need a baseline to compare things to
        $user = new User();
        $user->name = 'Vehicle Owner';
        $user->save();

        // first level widgets
        $kid1 = new WidgetNonSti();
        $kid1->user()->associate($user);
        $this->assertTrue($kid1->save());

        $kid2 = new WidgetNonSti();
        $kid2->user()->associate($user);
        $this->assertTrue($kid2->save());

        $this->assertEquals(2, $user->plainWidgets()->count(), 'Expected two child widgets');

        // second level widgets
        $grandkid1 = new WidgetNonSti();
        $grandkid1->parentWidget()->associate($kid1);
        $this->assertTrue($grandkid1->save());

        $grandkid2 = new WidgetNonSti();
        $grandkid2->parentWidget()->associate($kid1);
        $this->assertTrue($grandkid2->save());

        $grandkid3 = new WidgetNonSti();
        $grandkid3->parentWidget()->associate($kid2);
        $this->assertTrue($grandkid3->save());

        $this->assertEquals(3, $user->kidPlainWidgets()->get()->count(), 'Expected three grandchild widgets');
    }

    public function testStiHasManyThroughDirectCount()
    {
        $user = new User();
        $user->name = 'Vehicle Owner';
        $user->save();

        // first level widgets
        $kid1 = new WidgetSti();
        $kid1->user()->associate($user);
        $this->assertTrue($kid1->save());

        $kid2 = new WidgetSti();
        $kid2->user()->associate($user);
        $this->assertTrue($kid2->save());

        $this->assertEquals(2, $user->stiWidgets()->count(), 'Expected two child widgets');

        // second level widgets
        $grandkid1 = new WidgetSti();
        $grandkid1->parentWidget()->associate($kid1);
        $this->assertTrue($grandkid1->save());

        $grandkid2 = new WidgetSti();
        $grandkid2->parentWidget()->associate($kid1);
        $this->assertTrue($grandkid2->save());

        $grandkid3 = new WidgetSti();
        $grandkid3->parentWidget()->associate($kid2);
        $this->assertTrue($grandkid3->save());

        $this->assertEquals(
            3,
            $user->kidStiWidgets()->whereNotIn('widgets.id', [])->count(),
            'Expected three grandchild widgets'
        );
    }

}
