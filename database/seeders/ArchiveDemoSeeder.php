<?php

namespace Database\Seeders;

use App\Models\Appendix;
use App\Models\Document;
use App\Models\DocumentRevision;
use App\Models\Incident;
use App\Models\ObservationLog;
use App\Models\ObjectAttachment;
use App\Models\ObjectEntity;
use App\Models\Personnel;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ArchiveDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'director@bno.local'],
            [
                'name' => 'Директор УМЗАИС',
                'codename' => 'ДИР-01',
                'password' => Hash::make('archive-demo'),
                'role' => 'director',
                'clearance_level' => 5,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'analyst@bno.local'],
            [
                'name' => 'Ведущий аналитик',
                'codename' => 'АНЛ-17',
                'password' => Hash::make('archive-demo'),
                'role' => 'analyst',
                'clearance_level' => 3,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'guest@bno.local'],
            [
                'name' => 'Гость архива',
                'codename' => 'ГСТ-00',
                'password' => Hash::make('archive-demo'),
                'role' => 'guest',
                'clearance_level' => 1,
            ]
        );

        $director = User::query()->where('email', 'director@bno.local')->first();

        $obj184 = ObjectEntity::query()->updateOrCreate(
            ['slug' => 'bno-184'],
            [
                'number' => 'БНО-184',
                'title' => 'Кабель неизвестной длины',
                'status' => 'АКТИВЕН',
                'threat_class' => 'УМ',
                'anomaly_type' => 'ФИЗ / ТЕХ',
                'containment_level' => 'А2',
                'stability_index' => 'Ст-2',
                'psi_index' => 'ПИ-1',
                'amnestic_index' => 'Ам-1',
                'interaction_index' => 'Вз-1',
                'description' => 'Технологический артефакт в виде гибкого кабеля без маркировки. При измерении длина зависит от направления размотки; звуковой след содержит двойной шаг.',
                'containment_protocol' => 'Экранированная камера №12. Допуск только при выключенном осветительном оборудовании сектора.',
                'history' => 'Изъят из распределительного щита НИИ «Октант», 1998 г.',
                'is_active' => true,
                'is_hidden' => false,
                'public_archive' => true,
                'clearance_level' => 1,
            ]
        );

        $obj092 = ObjectEntity::query()->updateOrCreate(
            ['slug' => 'bno-092'],
            [
                'number' => 'БНО-092',
                'title' => 'Квартира 414',
                'status' => 'НАБЛЮДЕНИЕ',
                'threat_class' => 'ОП',
                'anomaly_type' => 'ПРО / ФИЗ',
                'containment_level' => 'Б1',
                'stability_index' => 'Ст-3',
                'psi_index' => 'ПИ-2',
                'amnestic_index' => 'Ам-2',
                'interaction_index' => 'Вз-0',
                'description' => 'Жилое помещение с нестабильной геометрией. Дверной проём периодически меняет азимут относительно лестничной клетки.',
                'containment_protocol' => 'Дверь заварена, наблюдение дистанционное. Вход только по приказу начальника секции О.',
                'history' => 'Зафиксировано после серии пропаж жильцов. Легенда для гражданских — техническая авария.',
                'is_active' => true,
                'is_hidden' => false,
                'public_archive' => false,
                'clearance_level' => 2,
            ]
        );

        $obj311 = ObjectEntity::query()->updateOrCreate(
            ['slug' => 'bno-311'],
            [
                'number' => 'БНО-311',
                'title' => 'Папка',
                'status' => 'АКТИВЕН',
                'threat_class' => 'НС',
                'anomaly_type' => 'ИНФ / ПСИ',
                'containment_level' => 'Б2',
                'stability_index' => 'Ст-4',
                'psi_index' => 'ПИ-3',
                'amnestic_index' => 'Ам-3',
                'interaction_index' => 'Вз-1',
                'description' => 'Картонная папка с неопределённым числом листов. При фотофиксации число страниц не совпадает между операторами.',
                'containment_protocol' => 'Хранение в сейфе S-II с автоматическим журналированием открытий. После аддендума 2023 — дополнительная звукоизоляция.',
                'history' => 'Поступила из архива упразднённого районного отдела внутренних дел. Первая регистрация в БНО — 1996 г.',
                'is_active' => true,
                'is_hidden' => false,
                'public_archive' => false,
                'clearance_level' => 2,
                'dynamic_state' => ['notes' => 'флуктуации инвентарных данных'],
            ]
        );

        ObjectEntity::query()->updateOrCreate(
            ['slug' => 'bno-055'],
            [
                'number' => 'БНО-055',
                'title' => 'Телефонная трубка (образец СУ)',
                'status' => 'ИЗОЛИРОВАН',
                'threat_class' => 'СУ',
                'anomaly_type' => 'ТЕХ / ПСИ',
                'containment_level' => 'СУ',
                'stability_index' => 'Ст-4',
                'psi_index' => 'ПИ-4',
                'amnestic_index' => 'Ам-4',
                'interaction_index' => 'Вз-3',
                'description' => '[[[CLASSIFIED]]] Детали переданы только директорату. Известно о массовой потере вербальной координации при прослушивании линии.',
                'containment_protocol' => 'Распределённое хранение компонентов. Сборка запрещена.',
                'history' => 'Происхождение засекречено.',
                'is_active' => true,
                'is_hidden' => true,
                'public_archive' => false,
                'clearance_level' => 5,
            ]
        );

        DB::table('object_entity_links')->upsert(
            [
                [
                    'object_entity_id' => $obj311->id,
                    'related_object_entity_id' => $obj092->id,
                    'relation_type' => 'резонанс',
                    'clearance_level' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'object_entity_id' => $obj184->id,
                    'related_object_entity_id' => $obj311->id,
                    'relation_type' => 'совместное наблюдение',
                    'clearance_level' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ],
            ['object_entity_id', 'related_object_entity_id', 'relation_type'],
            ['clearance_level', 'updated_at']
        );

        Incident::query()->updateOrCreate(
            ['title' => 'Утечка в Чехове-7'],
            [
                'object_entity_id' => null,
                'description' => 'Первичный инцидент с объектом условно БНО-001. Исчезновение построек, утрата памяти персонала.',
                'severity' => 'КТ',
                'casualties' => '47 по первичным данным',
                'occurred_on' => '1990-03-12',
                'location' => 'Подмосковье, военный городок',
                'clearance_level' => 1,
            ]
        );

        Incident::query()->updateOrCreate(
            ['title' => 'Несоответствие сканов БНО-311'],
            [
                'object_entity_id' => $obj311->id,
                'description' => 'Сканер №4 зафиксировал различие числа листов между двумя последовательными прогонами.',
                'severity' => 'СРЕДНИЙ',
                'casualties' => 'нет',
                'occurred_on' => '2023-09-02',
                'location' => 'Архив НИЦ-4',
                'clearance_level' => 2,
            ]
        );

        Incident::query()->updateOrCreate(
            ['title' => 'Двойной шаг при размотке БНО-184'],
            [
                'object_entity_id' => $obj184->id,
                'description' => 'Исполнитель отметил акустический артефакт, совпадающий с шагом наблюдателя.',
                'severity' => 'УМЕРЕННЫЙ',
                'casualties' => 'нет',
                'occurred_on' => '2011-06-14',
                'location' => 'Камера №12',
                'clearance_level' => 1,
            ]
        );

        ObservationLog::query()->where('object_entity_id', $obj311->id)->delete();
        foreach ([
            ['Стабильное состояние обложки.', 1],
            ['Флуктуация числа страниц +7 относительно предыдущей инвентаризации.', 2],
            ['Оператор сообщил о шёпоте при перелистывании (запись отсутствует).', 2],
        ] as $entry) {
            ObservationLog::query()->create([
                'object_entity_id' => $obj311->id,
                'logged_at' => now()->subDays(random_int(5, 120)),
                'entry' => $entry[0],
                'clearance_level' => $entry[1],
            ]);
        }

        Appendix::query()->where('object_entity_id', $obj311->id)->delete();
        Appendix::query()->create([
            'object_entity_id' => $obj311->id,
            'label' => 'Аддендум 2023‑Б',
            'body' => 'После инцидента 02.09.2023 все операции со сканированием перевести на аппаратуру без сетевых интерфейсов.',
            'clearance_level' => 2,
            'sort_order' => 10,
            'corrupted' => false,
        ]);
        Appendix::query()->create([
            'object_entity_id' => $obj311->id,
            'label' => 'Повреждённая выписка',
            'body' => 'Настоящим [[[CLASSIFIED]]] персонал секции [[[CLASSIFIED]]] обязан [[[CLASSIFIED]]] до выяснения.',
            'clearance_level' => 3,
            'sort_order' => 20,
            'corrupted' => true,
        ]);

        ObjectAttachment::query()->updateOrCreate(
            ['object_entity_id' => $obj184->id, 'path' => '/archive/bno-demo-cable.svg'],
            [
                'label' => 'Имитация скана маркировки',
                'kind' => 'scan',
                'clearance_level' => 1,
                'sort_order' => 10,
            ]
        );

        ObjectAttachment::query()->updateOrCreate(
            ['object_entity_id' => $obj311->id, 'path' => '/archive/bno-demo-folder.svg'],
            [
                'label' => 'Фото описи (нестабильный контраст)',
                'kind' => 'image',
                'clearance_level' => 2,
                'sort_order' => 5,
            ]
        );

        $docPrikaz = Document::query()->updateOrCreate(
            ['slug' => 'prikaz-k014'],
            [
                'title' => 'Приказ №К‑014 о режиме доступа к архиву',
                'type' => 'приказ',
                'content' => '1. Все сеансы фиксируются журналом аудита.'."\n"
                    .'2. Уровни допуска 0–5 не подлежат обсуждению по открытым каналам.'."\n"
                    .'3. [[[CLASSIFIED]]]',
                'classification' => 'ДСП',
                'author_id' => $director?->id,
                'clearance_level' => 1,
                'is_redacted' => true,
                'volatile' => false,
                'published_at' => now()->subMonths(6),
                'revision_label' => '3.4',
                'stamp' => 'sekretno',
                'registration_number' => 'АР/04-11/2003-17',
                'routing_note' => 'Сектор О → Архив АР → подпись УМЗАИС',
                'acknowledgment_stub' => 'Ознакомлен: начальник смены регистратуры (подпись не воспроизводится)',
                'marginalia' => "[КОММЕНТАРИЙ УДАЛЁН]\n— Кто вообще допустил это в архив?",
                'corruption_level' => 1,
                'chronology_anomaly' => false,
                'leaked_publish' => false,
                'references_object_slug' => null,
            ]
        );

        if (! DocumentRevision::query()->where('document_id', $docPrikaz->id)->exists()) {
            DocumentRevision::query()->create([
                'document_id' => $docPrikaz->id,
                'revision_label' => '3.3',
                'revised_at' => now()->subYears(3),
                'editor_id' => $director?->id,
                'change_reason' => 'После инцидента в Туле протокол изоляции обновлён',
                'content_snapshot' => "1. Все сеансы фиксируются журналом аудита.\n2. Уровни допуска не подлежат огласке.",
            ]);
        }

        Document::query()->updateOrCreate(
            ['slug' => 'forma-zaiavki'],
            [
                'title' => 'Форма заявки на перемещение объекта',
                'type' => 'форма',
                'content' => 'Поля: номер объекта, классификация полная строкой, причина перемещения, подпись начальника секции.',
                'classification' => 'ВНУТР.',
                'author_id' => $director?->id,
                'clearance_level' => 2,
                'is_redacted' => false,
                'volatile' => false,
                'published_at' => now()->subYears(2),
                'revision_label' => '1.2',
                'stamp' => 'dsp',
                'registration_number' => 'АР/Ф-218/1999',
                'routing_note' => 'Исполнитель → сектор О → архив',
                'acknowledgment_stub' => null,
                'marginalia' => null,
                'corruption_level' => 0,
                'chronology_anomaly' => false,
                'leaked_publish' => false,
                'references_object_slug' => 'bno-524',
            ]
        );

        Document::query()->updateOrCreate(
            ['slug' => 'otchёт-lv'],
            [
                'title' => 'Отчёт о нестабильности узла LV‑07',
                'type' => 'отчёт',
                'content' => 'Узел демонстрирует потерю блоков текста без записи в журнал транзакций. Рекомендации: локальный режим, контрольная сумма каждые 12 часов.'."\n\n"
                    .'Выписка из журнала наблюдения (1998): «Не допускать контакта с БНО-311 после инцидента 2023».',
                'classification' => 'ВНУТР.',
                'author_id' => $director?->id,
                'clearance_level' => 2,
                'is_redacted' => false,
                'volatile' => true,
                'published_at' => now()->subWeek(),
                'revision_label' => '2.0',
                'stamp' => 'povrezhden',
                'registration_number' => 'Л/ЖУР-1998-перенос',
                'routing_note' => 'Лабораторный комплекс · только машиночитаемая копия',
                'acknowledgment_stub' => null,
                'marginalia' => 'Ошибка синхронизации меток · см. примечание УМЗАИС',
                'corruption_level' => 2,
                'chronology_anomaly' => true,
                'leaked_publish' => false,
                'references_object_slug' => 'bno-311',
            ]
        );

        Personnel::query()->updateOrCreate(
            ['codename' => 'ИВОЛГА'],
            [
                'real_name' => '███ ██████ ████████',
                'rank' => 'Начальник секции АР',
                'division' => 'АР',
                'profile_type' => 'Устойчивый / конформный',
                'clearance_level' => 4,
                'status' => 'действующий',
                'biography' => 'Курирует регистрацию объектов с 1999 года. Отмечена дисциплинированностью и склонностью к ночным сменам.',
                'deceased' => false,
            ]
        );

        Personnel::query()->updateOrCreate(
            ['codename' => 'КУРЬЕР'],
            [
                'real_name' => null,
                'rank' => 'Младший оператор регистратуры',
                'division' => 'АР',
                'profile_type' => 'Нормотипический',
                'clearance_level' => 1,
                'status' => 'действующий',
                'biography' => 'Ответственен за бумажный документооборот и выдачу форм. Доступ к полевым операциям отсутствует.',
                'deceased' => false,
            ]
        );

        Personnel::query()->updateOrCreate(
            ['codename' => 'КРОТ'],
            [
                'real_name' => null,
                'rank' => 'Оперуполномоченный секции О',
                'division' => 'О',
                'profile_type' => 'Гиперконтроль / тревожный',
                'clearance_level' => 3,
                'status' => 'действующий',
                'biography' => 'Специализация — полевые изъятия. Имеет временные ограничения на работу с ПСИ‑объектами после инцидента в Казани.',
                'deceased' => false,
            ]
        );

        Personnel::query()->updateOrCreate(
            ['codename' => 'СУГДЕЙ'],
            [
                'real_name' => null,
                'rank' => 'Ведущий лаборант',
                'division' => 'Л',
                'profile_type' => 'Диссоциативный / высокий IQ',
                'clearance_level' => 3,
                'status' => 'стажировка прекращена',
                'biography' => 'Пропал без вести после работы с БНО-311. Официальная версия — отпуск по состоянию здоровья.',
                'deceased' => true,
            ]
        );

        foreach (config('bno.home_terminal') as $key => $meta) {
            SiteContent::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $meta['label'], 'body' => $meta['default']]
            );
        }

        Document::query()->updateOrCreate(
            ['slug' => 'arkhiv-kak-instrument'],
            [
                'title' => 'Архив как инструмент сдерживания',
                'type' => 'статья',
                'content' => 'Краткий служебный комментарий: доступ к описи не означает понимание объекта. Любые попытки построить полную картину по фрагментам классифицируются как риск для узла.',
                'classification' => 'ВНУТР.',
                'author_id' => $director?->id,
                'clearance_level' => 1,
                'is_redacted' => false,
                'volatile' => false,
                'published_at' => now()->subDays(4),
                'revision_label' => '1.0',
                'stamp' => 'arhivnaya_kopiya',
                'registration_number' => null,
                'routing_note' => null,
                'acknowledgment_stub' => null,
                'marginalia' => null,
                'corruption_level' => 0,
                'chronology_anomaly' => false,
                'leaked_publish' => true,
                'references_object_slug' => null,
            ]
        );
    }
}
