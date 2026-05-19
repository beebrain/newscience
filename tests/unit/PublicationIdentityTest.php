<?php

use App\Libraries\PublicationIdentity;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PublicationIdentityTest extends CIUnitTestCase
{
    public function testPublicationExternalKeyFromRrApiShapeMatchesBareDoi(): void
    {
        $doi = ' 10.1000/TEST ';
        $key = PublicationIdentity::publicationExternalKeyFromRrApiShape($doi, 99);
        $expected = 'pub:h:' . substr(hash('sha256', strtolower(trim($doi))), 0, 40);
        $this->assertSame($expected, $key);
    }

    public function testPublicationExternalKeyFromRrApiShapeUsesIdWhenDoiEmpty(): void
    {
        $this->assertSame('pub:id:42', PublicationIdentity::publicationExternalKeyFromRrApiShape('', 42));
    }

    public function testNormalizeDoiStripsUrlAndLowercases(): void
    {
        $n = PublicationIdentity::normalizeDoi(' https://DOI.org/10.1000/AbC ');
        $this->assertSame('10.1000/abc', $n);
        $key = PublicationIdentity::publicationExternalKeyFromNormalizedDoi($n);
        $rr  = PublicationIdentity::publicationExternalKeyFromRrApiShape('10.1000/abc', 0);
        $this->assertSame($rr, $key);
    }

    public function testPublicationExternalKeyFromDoiEmpty(): void
    {
        $this->assertNull(PublicationIdentity::publicationExternalKeyFromDoi(''));
        $this->assertNull(PublicationIdentity::publicationExternalKeyFromDoi('   '));
    }

    public function testPublicationExternalKeyFromRrId(): void
    {
        $this->assertSame('pub:id:7', PublicationIdentity::publicationExternalKeyFromRrId(7));
        $this->assertNull(PublicationIdentity::publicationExternalKeyFromRrId(0));
    }

    public function testSyncExternalKeyFromMetadataPrefersStoredKey(): void
    {
        $meta = ['sync_external_key' => 'pub:id:5', 'doi' => '10.1/x'];
        $this->assertSame('pub:id:5', PublicationIdentity::syncExternalKeyFromMetadata($meta));
    }

    public function testSyncExternalKeyFromMetadataFallsBackToDoiKey(): void
    {
        $meta = ['doi' => '10.2000/zz'];
        $n    = PublicationIdentity::normalizeDoi('10.2000/zz');
        $this->assertSame(
            PublicationIdentity::publicationExternalKeyFromNormalizedDoi($n),
            PublicationIdentity::syncExternalKeyFromMetadata($meta)
        );
    }

    public function testSyncExternalKeyFromMetadataFallsBackToRrId(): void
    {
        $meta = ['rr_publication_id' => 99];
        $this->assertSame('pub:id:99', PublicationIdentity::syncExternalKeyFromMetadata($meta));
    }
}
