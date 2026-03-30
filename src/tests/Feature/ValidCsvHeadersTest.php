<?php

use App\Rules\ValidCsvHeaders;
use Illuminate\Http\UploadedFile;

it('passes for non-csv uploads (mixed endpoint)', function () {
    $file = UploadedFile::fake()->createWithContent('notes.txt', "just some text\n");

    $validator = validator(['file' => $file], [
        'file' => [new ValidCsvHeaders()],
    ]);

    expect($validator->fails())->toBeFalse();
});

it('fails when csv contains empty column headers', function () {
    $file = UploadedFile::fake()->createWithContent('bad.csv', "Name,,Email\nJohn,,john@example.com\n");

    $validator = validator(['file' => $file], [
        'file' => [new ValidCsvHeaders()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('file'))->toContain('empty column headers');
});

it('fails when csv contains duplicate column names (case-insensitive)', function () {
    $file = UploadedFile::fake()->createWithContent('dup.csv', "Name,Email,email\nJohn,a@b.com,c@d.com\n");

    $validator = validator(['file' => $file], [
        'file' => [new ValidCsvHeaders()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('file'))->toContain('duplicate column names');
});

it('fails when csv contains reserved managed timestamp headers', function () {
    $file = UploadedFile::fake()->createWithContent('reserved.csv', "created_at_ts,updated_at_ts\n2026-01-01 00:00:00,2026-01-01 00:00:00\n");

    $validator = validator(['file' => $file], [
        'file' => [new ValidCsvHeaders()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('file'))->toContain('created_at_ts');
    expect($validator->errors()->first('file'))->toContain('updated_at_ts');
});

it('fails when csv headers normalize to reserved managed timestamp headers', function () {
    $file = UploadedFile::fake()->createWithContent('reserved-normalized.csv', "Created At,Updated At Ts\n2026-01-01 00:00:00,2026-01-01 00:00:00\n");

    $validator = validator(['file' => $file], [
        'file' => [new ValidCsvHeaders()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('file'))->toContain('created_at_ts');
    expect($validator->errors()->first('file'))->toContain('updated_at_ts');
});

it('rejects a pdf renamed to csv', function () {
    $file = UploadedFile::fake()->createWithContent('fake.csv', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n");

    $validator = validator(['file' => $file], [
        'file' => [new ValidCsvHeaders()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('file'))->toContain('must be a CSV file');
});

it('passes for valid, unique, non-empty csv headers', function () {
    $file = UploadedFile::fake()->createWithContent('good.csv', "Name,Email\nJohn,john@example.com\n");

    $validator = validator(['file' => $file], [
        'file' => [new ValidCsvHeaders()],
    ]);

    expect($validator->fails())->toBeFalse();
});