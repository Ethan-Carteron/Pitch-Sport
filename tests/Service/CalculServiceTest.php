<?php

namespace App\Tests\Service;

use App\Entity\Player;
use App\Entity\Workload;
use App\Repository\PlayerRepository;
use App\Repository\WorkloadRepository;
use App\Service\CalculService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CalculServiceTest extends TestCase
{
    private WorkloadRepository $workloadRepository;
    private PlayerRepository $playerRepository;
    private CalculService $calculService;

    protected function setUp(): void
    {
        $this->workloadRepository = $this->createMock(WorkloadRepository::class);
        $this->playerRepository = $this->createMock(PlayerRepository::class);
        $this->calculService = new CalculService($this->workloadRepository, $this->playerRepository);
    }

    // ==========================================
    // 1. CAS LIMITES ET DIVISIONS PAR ZÉRO
    // ==========================================

    public function testCalculAcwrReturnsNullWhenChronicLoadIsZero(): void
    {
        $player = new Player();

        $this->workloadRepository->method('findRecentWorkloads')->willReturn([]);

        $result = $this->calculService->calculAcwr($player);

        $this->assertNull($result);
    }

    public function testCalculFosterMonotonyReturnsNullIfLessThanTwoSessions(): void
    {
        $player = new Player();
        $workload = $this->createMock(Workload::class);
        $workload->method('getCharge')->willReturn(500.0);

        $this->workloadRepository->method('findRecentWorkloads')->willReturn([$workload]);

        $result = $this->calculService->calculFosterMonotony($player);

        $this->assertNull($result);
    }

    public function testCalculFosterMonotonyReturnsNullIfEcartTypeIsZero(): void
    {
        $player = new Player();
        $firstWorkload = $this->createMock(Workload::class);
        $firstWorkload->method('getCharge')->willReturn(500.0);
        $secondWorkload = $this->createMock(Workload::class);
        $secondWorkload->method('getCharge')->willReturn(500.0);

        $this->workloadRepository->method('findRecentWorkloads')->willReturn([$firstWorkload, $secondWorkload]);

        $result = $this->calculService->calculFosterMonotony($player);

        $this->assertNull($result);
    }

    // ==========================================
    // 2. ALGORITHMES MATHÉMATIQUES
    // ==========================================

    public function testCalculFosterMonotonyMathematicalLogic(): void
    {
        $player = new Player();
        $firstWorkload = $this->createMock(Workload::class);
        $firstWorkload->method('getCharge')->willReturn(100.0);
        $secondWorkload = $this->createMock(Workload::class);
        $secondWorkload->method('getCharge')->willReturn(300.0);

        $this->workloadRepository->method('findRecentWorkloads')->willReturn([$firstWorkload, $secondWorkload]);

        $result = $this->calculService->calculFosterMonotony($player);

        $this->assertEquals(2.0, $result);
    }

    public function testCalculAcwrLogic(): void
    {
        $player = new Player();
        $acuteWorkload = $this->createMock(Workload::class);
        $acuteWorkload->method('getCharge')->willReturn(1500.0);
        $chronicWorkload = $this->createMock(Workload::class);
        $chronicWorkload->method('getCharge')->willReturn(1000.0);

        $this->workloadRepository->method('findRecentWorkloads')
            ->willReturnOnConsecutiveCalls([$acuteWorkload], [$chronicWorkload]);

        $result = $this->calculService->calculAcwr($player);

        $this->assertEquals(1.5, $result);
    }

    // ==========================================
    // 3. FRONTIÈRES DES SEUILS (BOUNDARY TESTS)
    // ==========================================

    #[DataProvider('fosterAlertLevelProvider')]
    public function testGetFosterAlertLevel(?float $foster, ?int $expectedLevel): void
    {
        $result = $this->calculService->getFosterAlertLevel($foster);

        $this->assertEquals($expectedLevel, $result);
    }

    public static function fosterAlertLevelProvider(): array
    {
        return [
            [null, null],
            [1.5, CalculService::ALERT_GREEN],
            [1.99, CalculService::ALERT_GREEN],
            [2.0, CalculService::ALERT_ORANGE],
            [2.49, CalculService::ALERT_ORANGE],
            [2.5, CalculService::ALERT_RED],
            [3.0, CalculService::ALERT_RED],
        ];
    }

    #[DataProvider('acwrAlertLevelProvider')]
    public function testGetAcwrAlertLevel(?float $acwr, ?int $expectedLevel): void
    {
        $result = $this->calculService->getAcwrAlertLevel($acwr);

        $this->assertEquals($expectedLevel, $result);
    }

    public static function acwrAlertLevelProvider(): array
    {
        return [
            [null, null],
            [0.6, CalculService::ALERT_RED],
            [0.7, CalculService::ALERT_RED],
            [0.79, CalculService::ALERT_ORANGE],
            [0.8, CalculService::ALERT_GREEN],
            [1.0, CalculService::ALERT_GREEN],
            [1.3, CalculService::ALERT_GREEN],
            [1.31, CalculService::ALERT_ORANGE],
            [1.49, CalculService::ALERT_ORANGE],
            [1.5, CalculService::ALERT_RED],
            [1.8, CalculService::ALERT_RED],
        ];
    }

    // ==========================================
    // 4. SCORE DE RISQUE
    // ==========================================

    public function testCalculRiskScoreReturnsCorrectWeightedValue(): void
    {
        $player = new Player();
        $calculServiceMock = $this->getMockBuilder(CalculService::class)
            ->setConstructorArgs([$this->workloadRepository, $this->playerRepository])
            ->onlyMethods(['calculAcwr', 'calculVmaxDrop', 'calculFosterMonotony'])
            ->getMock();

        $calculServiceMock->method('calculAcwr')->willReturn(2.0);
        $calculServiceMock->method('calculVmaxDrop')->willReturn(0.0);
        $calculServiceMock->method('calculFosterMonotony')->willReturn(1.0);

        $score = $calculServiceMock->calculRiskScore($player);

        $this->assertEquals(33, $score);
    }
}
