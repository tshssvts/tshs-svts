<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OffensesWithSanctionSeeder extends Seeder
{
    public function run(): void
    {
        $offenses = [
            [
                'offense_type' => 'Tardiness',
                'offense_description' => 'Late arrival to class',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Incomplete homework',
                'offense_description' => 'Failure to submit assigned homework',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Disruptive behavior',
                'offense_description' => 'Behaviors that disrupt the learning environment',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Bullying/harassment',
                'offense_description' => 'Intimidation, teasing, or harassment of others',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Cheating/plagiarism',
                'offense_description' => 'Unauthorized use of others\' work or ideas',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Truancy',
                'offense_description' => 'Unexcused absence from school',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Substance abuse',
                'offense_description' => 'Use or possession of prohibited substances',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Physical aggression',
                'offense_description' => 'Physical harm or threat to others',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Theft',
                'offense_description' => 'Stealing or unauthorized taking of property',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Vandalism',
                'offense_description' => 'Willful destruction or damage to property',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Unauthorized technology use',
                'offense_description' => 'Improper or unauthorized use of technology',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Defiance/resisting authority',
                'offense_description' => 'Refusal to follow instructions or obey authority',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                ],
            ],
            [
                'offense_type' => 'Dress code violation',
                'offense_description' => 'Failure to comply with school dress code',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Academic dishonesty',
                'offense_description' => 'Academic dishonesty or cheating',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Disrespectful language',
                'offense_description' => 'Rude or offensive language towards others',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Forgery/falsification',
                'offense_description' => 'Forging or falsifying documents or signatures',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Cyberbullying',
                'offense_description' => 'Bullying or harassment through electronic means',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Gambling',
                'offense_description' => 'Participating in games of chance or betting',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Destruction of property',
                'offense_description' => 'Deliberate damage to school or personal property',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Hate speech',
                'offense_description' => 'Offensive language targeting specific groups',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Excessive noise',
                'offense_description' => 'Disruptive noise levels that interfere with learning',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Skipping class',
                'offense_description' => 'Unauthorized absence from class or school',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Academic misconduct',
                'offense_description' => 'Violation of academic integrity',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Verbal harassment',
                'offense_description' => 'Harassment through spoken words',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Plagiarism',
                'offense_description' => 'Using someone else\'s work without proper attribution',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Inappropriate use of social media',
                'offense_description' => 'Misuse or violation of social media guidelines',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Littering',
                'offense_description' => 'Improper disposal of waste materials',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Skipping school',
                'offense_description' => 'Unexcused absence from entire school day',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Forgery/faking signatures',
                'offense_description' => 'Forging or faking signatures on documents',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Discrimination',
                'offense_description' => 'Unfair treatment based on characteristics',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Unauthorized use of school equipment',
                'offense_description' => 'Improper or unauthorized use of school equipment',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Inappropriate physical contact',
                'offense_description' => 'Unwanted or inappropriate physical contact',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Unauthorized materials',
                'offense_description' => 'Possession or use of prohibited items',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                ],
            ],
            [
                'offense_type' => 'Threats or intimidation',
                'offense_description' => 'Expressing intent to harm or intimidate others',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
            [
                'offense_type' => 'Use of profanity',
                'offense_description' => 'Use of offensive or vulgar language',
                'sanctions' => [
                    ['Verbal Warning', 1, 3],
                    ['Detention', 2, 1],
                    ['Parent Notification', 3, 1],
                    ['Restorative Action', 4, 1],
                    ['Counseling', 5, 1],
                    ['Suspension', 6, 1],
                    ['Expulsion', 7, 1],
                ],
            ],
        ];

        foreach ($offenses as $offense) {
            foreach ($offense['sanctions'] as [$sanction, $group, $max_stage]) {
                for ($stage = 1; $stage <= $max_stage; $stage++) {
                    DB::table('tbl_offenses_with_sanction')->insert([
                        'offense_type' => $offense['offense_type'],
                        'offense_description' => $offense['offense_description'],
                        'sanction_consequences' => $sanction,
                        'group_number' => $group,
                        'stage_number' => $stage,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
