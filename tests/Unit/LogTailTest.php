<?php

namespace Tests\Unit;

use App\Support\LogTail;
use PHPUnit\Framework\TestCase;

class LogTailTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = tempnam(sys_get_temp_dir(), 'logtail');
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
        parent::tearDown();
    }

    private function write(string $contents): void
    {
        file_put_contents($this->path, $contents);
    }

    public function test_it_returns_the_last_lines_in_order(): void
    {
        $this->write(implode("\n", ['un', 'deux', 'trois', 'quatre', 'cinq'])."\n");

        $this->assertSame(['trois', 'quatre', 'cinq'], LogTail::read($this->path, 3));
    }

    public function test_it_returns_every_line_when_the_file_is_shorter_than_requested(): void
    {
        $this->write("alpha\nbeta\n");

        $this->assertSame(['alpha', 'beta'], LogTail::read($this->path, 100));
    }

    public function test_it_spans_multiple_chunks_without_losing_or_splitting_lines(): void
    {
        // 500 lignes largement au-delà d'un bloc de 64 octets : la lecture
        // doit reculer plusieurs fois sans couper une ligne en deux.
        $lines = array_map(static fn (int $i) => "ligne-{$i}", range(1, 500));
        $this->write(implode("\n", $lines)."\n");

        $tail = LogTail::read($this->path, 120, chunkSize: 64);

        $this->assertCount(120, $tail);
        $this->assertSame('ligne-381', $tail[0]);
        $this->assertSame('ligne-500', $tail[119]);
    }

    public function test_it_reads_a_file_with_no_trailing_newline(): void
    {
        $this->write("premiere\nderniere");

        $this->assertSame(['premiere', 'derniere'], LogTail::read($this->path, 10));
    }

    public function test_it_skips_blank_lines(): void
    {
        $this->write("a\n\n\nb\n");

        $this->assertSame(['a', 'b'], LogTail::read($this->path, 10));
    }

    public function test_it_returns_nothing_for_a_missing_file(): void
    {
        $this->assertSame([], LogTail::read('/chemin/qui/nexiste/pas.log'));
    }

    public function test_it_returns_nothing_for_an_empty_file(): void
    {
        $this->write('');

        $this->assertSame([], LogTail::read($this->path, 10));
    }
}
