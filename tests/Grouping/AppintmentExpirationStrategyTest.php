<?php

namespace App\Tests\Grouping;

use App\Entity\Appointment;
use App\Grouping\AppointmentDateGroup;
use App\Grouping\AppointmentExpirationGroup;
use App\Grouping\AppointmentExpirationStrategy;
use App\Grouping\Grouper;
use PHPUnit\Framework\TestCase;
use SchoolIT\CommonBundle\Helper\DateHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppintmentExpirationStrategyTest extends TestCase {

    private function getTestData() {
        $dates = [
            '2018-12-24',
            '2018-12-25',
            '2018-12-24',
            '2018-12-31',
            '2019-01-01',
            '2019-02-01',
            '2019-02-02',
            '2019-03-02'
        ];

        $appointments = [ ];

        foreach($dates as $startDate) {
            $appointments[] = (new Appointment())
                ->setAllDay(true)
                ->setEnd(new \DateTime($startDate));
        }

        return $appointments;
    }

    public function testAppointmentEndingTodayIsNotExpired() {
        $dateHelper = new DateHelper();
        $dateHelper->setToday(new \DateTime('2019-01-02'));

        $strategy = new AppointmentExpirationStrategy($dateHelper);

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock
            ->method('get')
            ->with(AppointmentExpirationStrategy::class)
            ->willReturn($strategy);

        $grouper = new Grouper();
        $grouper->setContainer($containerMock);

        $appointment = (new Appointment())
            ->setAllDay(true)
            ->setEnd(new \DateTime('2019-01-02'));
        /** @var AppointmentExpirationGroup[] $groups */
        $groups = $grouper->group([$appointment], AppointmentExpirationStrategy::class);

        $this->assertEquals(1, count($groups));
        $group = $groups[0];

        $this->assertFalse($group->isExpired());
    }

    public function testGrouping() {
        $dateHelper = new DateHelper();
        $dateHelper->setToday(new \DateTime('2019-02-01'));

        $strategy = new AppointmentExpirationStrategy($dateHelper);

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock
            ->method('get')
            ->with(AppointmentExpirationStrategy::class)
            ->willReturn($strategy);

        $grouper = new Grouper();
        $grouper->setContainer($containerMock);

        $array = $this->getTestData();
        /** @var AppointmentExpirationGroup[] $groups */
        $groups = $grouper->group($array, AppointmentExpirationStrategy::class);

        $this->assertEquals(2, count($groups));

        $firstGroup = $groups[0];
        $this->assertTrue($firstGroup->isExpired());
        $this->assertEquals(5, count($firstGroup->getAppointments()));

        $secondGroup = $groups[1];
        $this->assertFalse($secondGroup->isExpired());
        $this->assertEquals(3, count($secondGroup->getAppointments()));
    }
}