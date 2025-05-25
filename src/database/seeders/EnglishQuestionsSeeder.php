<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnglishQuestionsSeeder extends Seeder
{
    public function run()
    {
       
        $questions = [
            // Easy Questions (20)
            [
                'question_text' => 'Which of the following is a correct sentence?',
                'choices' => [
                    'Me and him went to the store.',
                    'He and I went to the store.',
                    'Him and I went to the store.',
                    'Me and he went to the store.'
                ],
                'correct_choice' => 2,
                'explanation' => 'When using multiple subjects, use subject pronouns (I, he, she, we, they) and put yourself last.',
                'difficulty_level' => 'easy',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the past tense of "go"?',
                'choices' => [
                    'goed',
                    'went',
                    'gone',
                    'going'
                ],
                'correct_choice' => 2,
                'explanation' => 'The past tense of "go" is "went". "Gone" is the past participle.',
                'difficulty_level' => 'easy',
                'category' => 'Vocabulary'
            ],
            [
                'question_text' => 'Which word is a noun?',
                'choices' => [
                    'run',
                    'beautiful',
                    'quickly',
                    'happiness'
                ],
                'correct_choice' => 4,
                'explanation' => 'Happiness is a noun. Run is a verb, beautiful is an adjective, and quickly is an adverb.',
                'difficulty_level' => 'easy',
                'category' => 'Parts of Speech'
            ],
            [
                'question_text' => 'What is the opposite of "happy"?',
                'choices' => [
                    'sad',
                    'angry',
                    'tired',
                    'excited'
                ],
                'correct_choice' => 1,
                'explanation' => 'The opposite of happy is sad. While angry, tired, and excited are also emotions, they are not direct opposites.',
                'difficulty_level' => 'easy',
                'category' => 'Vocabulary'
            ],
            [
                'question_text' => 'Which sentence is a question?',
                'choices' => [
                    'The cat is sleeping.',
                    'Where is the cat?',
                    'The cat is here.',
                    'Look at the cat.'
                ],
                'correct_choice' => 2,
                'explanation' => 'Questions typically begin with question words (what, where, when, why, how) or auxiliary verbs, and end with a question mark.',
                'difficulty_level' => 'easy',
                'category' => 'Sentence Structure'
            ],
            [
                'question_text' => 'What is the plural of "book"?',
                'choices' => [
                    'bookes',
                    'books',
                    'bookies',
                    'booken'
                ],
                'correct_choice' => 2,
                'explanation' => 'Most nouns form their plural by adding -s to the singular form.',
                'difficulty_level' => 'easy',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which word is an adjective?',
                'choices' => [
                    'run',
                    'beautiful',
                    'quickly',
                    'book'
                ],
                'correct_choice' => 2,
                'explanation' => 'Beautiful is an adjective that describes a noun. Run is a verb, quickly is an adverb, and book is a noun.',
                'difficulty_level' => 'easy',
                'category' => 'Parts of Speech'
            ],
            [
                'question_text' => 'What is the correct contraction for "they are"?',
                'choices' => [
                    'theyre',
                    'their',
                    'they\'re',
                    'there'
                ],
                'correct_choice' => 3,
                'explanation' => 'They\'re is the contraction of "they are". Their is a possessive pronoun, and there is an adverb.',
                'difficulty_level' => 'easy',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which sentence uses correct punctuation?',
                'choices' => [
                    'The cat, dog and bird are pets.',
                    'The cat, dog, and bird are pets.',
                    'The cat dog and bird are pets.',
                    'The cat; dog; and bird are pets.'
                ],
                'correct_choice' => 2,
                'explanation' => 'In a list of three or more items, use commas to separate the items, including before the final "and".',
                'difficulty_level' => 'easy',
                'category' => 'Punctuation'
            ],
            [
                'question_text' => 'What is the present tense of "went"?',
                'choices' => [
                    'go',
                    'goes',
                    'going',
                    'gone'
                ],
                'correct_choice' => 1,
                'explanation' => 'The present tense of "went" is "go". "Goes" is third person singular, "going" is present participle, and "gone" is past participle.',
                'difficulty_level' => 'easy',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which word is a preposition?',
                'choices' => [
                    'run',
                    'under',
                    'quickly',
                    'book'
                ],
                'correct_choice' => 2,
                'explanation' => 'Under is a preposition that shows the relationship between nouns. Run is a verb, quickly is an adverb, and book is a noun.',
                'difficulty_level' => 'easy',
                'category' => 'Parts of Speech'
            ],
            [
                'question_text' => 'What is the correct spelling?',
                'choices' => [
                    'recieve',
                    'receive',
                    'receeve',
                    'receve'
                ],
                'correct_choice' => 2,
                'explanation' => 'The correct spelling is "receive". Remember the rule: "i" before "e" except after "c".',
                'difficulty_level' => 'easy',
                'category' => 'Spelling'
            ],
            [
                'question_text' => 'Which sentence uses "their" correctly?',
                'choices' => [
                    'Their going to the store.',
                    'They\'re going to the store.',
                    'There going to the store.',
                    'They are going to the store.'
                ],
                'correct_choice' => 4,
                'explanation' => 'Their is a possessive pronoun. In this case, we need the contraction "they\'re" or the full form "they are".',
                'difficulty_level' => 'easy',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the opposite of "begin"?',
                'choices' => [
                    'start',
                    'end',
                    'continue',
                    'pause'
                ],
                'correct_choice' => 2,
                'explanation' => 'The opposite of begin is end. Start is a synonym, continue means to keep going, and pause means to temporarily stop.',
                'difficulty_level' => 'easy',
                'category' => 'Vocabulary'
            ],
            [
                'question_text' => 'Which word is an adverb?',
                'choices' => [
                    'run',
                    'beautiful',
                    'quickly',
                    'book'
                ],
                'correct_choice' => 3,
                'explanation' => 'Quickly is an adverb that modifies a verb. Run is a verb, beautiful is an adjective, and book is a noun.',
                'difficulty_level' => 'easy',
                'category' => 'Parts of Speech'
            ],
            [
                'question_text' => 'What is the correct plural of "baby"?',
                'choices' => [
                    'babys',
                    'babies',
                    'babyes',
                    'babys'
                ],
                'correct_choice' => 2,
                'explanation' => 'When a word ends in -y preceded by a consonant, change the -y to -ies to form the plural.',
                'difficulty_level' => 'easy',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which sentence is a command?',
                'choices' => [
                    'The cat is sleeping.',
                    'Where is the cat?',
                    'The cat is here.',
                    'Look at the cat!'
                ],
                'correct_choice' => 4,
                'explanation' => 'Commands (imperative sentences) give orders or instructions and often end with an exclamation mark.',
                'difficulty_level' => 'easy',
                'category' => 'Sentence Structure'
            ],
            [
                'question_text' => 'What is the correct form of "to be" for "he"?',
                'choices' => [
                    'am',
                    'is',
                    'are',
                    'be'
                ],
                'correct_choice' => 2,
                'explanation' => 'The correct form of "to be" for "he" is "is". "Am" is for "I", "are" is for "you/we/they".',
                'difficulty_level' => 'easy',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which word is a conjunction?',
                'choices' => [
                    'run',
                    'and',
                    'quickly',
                    'book'
                ],
                'correct_choice' => 2,
                'explanation' => 'And is a conjunction that connects words or phrases. Run is a verb, quickly is an adverb, and book is a noun.',
                'difficulty_level' => 'easy',
                'category' => 'Parts of Speech'
            ],
            [
                'question_text' => 'What is the correct spelling?',
                'choices' => [
                    'seperate',
                    'separate',
                    'seperrate',
                    'seperat'
                ],
                'correct_choice' => 2,
                'explanation' => 'The correct spelling is "separate". Remember: "There\'s a rat in separate."',
                'difficulty_level' => 'easy',
                'category' => 'Spelling'
            ],

            // Medium Questions (20)
            [
                'question_text' => 'Which sentence uses the subjunctive mood correctly?',
                'choices' => [
                    'If I was rich, I would buy a house.',
                    'If I were rich, I would buy a house.',
                    'If I am rich, I will buy a house.',
                    'If I be rich, I would buy a house.'
                ],
                'correct_choice' => 2,
                'explanation' => 'The subjunctive mood uses "were" instead of "was" for hypothetical situations, even with singular subjects.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the meaning of the idiom "to hit the nail on the head"?',
                'choices' => [
                    'To make a mistake',
                    'To be exactly right',
                    'To work hard',
                    'To cause trouble'
                ],
                'correct_choice' => 2,
                'explanation' => 'To hit the nail on the head means to be exactly right or to describe something perfectly.',
                'difficulty_level' => 'medium',
                'category' => 'Idioms'
            ],
            [
                'question_text' => 'Which sentence contains a dangling modifier?',
                'choices' => [
                    'Running quickly, the bus was caught by John.',
                    'John caught the bus while running quickly.',
                    'While running quickly, John caught the bus.',
                    'The bus was caught by John, who was running quickly.'
                ],
                'correct_choice' => 1,
                'explanation' => 'A dangling modifier occurs when the subject of the modifier is unclear or missing. In this case, it seems the bus was running quickly.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the correct plural form of "criterion"?',
                'choices' => [
                    'criterions',
                    'criteria',
                    'criterias',
                    'criterion'
                ],
                'correct_choice' => 2,
                'explanation' => 'Criteria is the plural form of criterion. It is a Greek-derived word that follows Greek pluralization rules.',
                'difficulty_level' => 'medium',
                'category' => 'Vocabulary'
            ],
            [
                'question_text' => 'Which sentence uses the passive voice?',
                'choices' => [
                    'The cat chased the mouse.',
                    'The mouse was chased by the cat.',
                    'The cat is chasing the mouse.',
                    'The cat will chase the mouse.'
                ],
                'correct_choice' => 2,
                'explanation' => 'The passive voice is formed using a form of "to be" plus the past participle of the main verb. The subject receives the action rather than performing it.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the meaning of the word "ambiguous"?',
                'choices' => [
                    'clear and definite',
                    'open to multiple interpretations',
                    'very specific',
                    'completely wrong'
                ],
                'correct_choice' => 2,
                'explanation' => 'Ambiguous means open to multiple interpretations or having more than one possible meaning.',
                'difficulty_level' => 'medium',
                'category' => 'Vocabulary'
            ],
            [
                'question_text' => 'Which sentence uses the correct parallel structure?',
                'choices' => [
                    'She likes swimming, to run, and biking.',
                    'She likes swimming, running, and biking.',
                    'She likes to swim, running, and to bike.',
                    'She likes to swim, to run, and biking.'
                ],
                'correct_choice' => 2,
                'explanation' => 'Parallel structure requires using the same grammatical form for all items in a list. In this case, all gerunds (-ing forms) are used.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the correct use of "farther" vs "further"?',
                'choices' => [
                    'I can\'t walk any further.',
                    'I can\'t walk any farther.',
                    'We need to farther investigate this matter.',
                    'The store is further down the road.'
                ],
                'correct_choice' => 2,
                'explanation' => 'Farther is used for physical distance, while further is used for metaphorical or figurative distance.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which sentence demonstrates correct use of the semicolon?',
                'choices' => [
                    'The cat is black; and the dog is white.',
                    'The cat is black; the dog is white.',
                    'The cat is black, the dog is white.',
                    'The cat is black; but the dog is white.'
                ],
                'correct_choice' => 2,
                'explanation' => 'A semicolon is used to join two independent clauses without a coordinating conjunction.',
                'difficulty_level' => 'medium',
                'category' => 'Punctuation'
            ],
            [
                'question_text' => 'What is the meaning of the idiom "to let the cat out of the bag"?',
                'choices' => [
                    'To release an animal',
                    'To reveal a secret',
                    'To make a mistake',
                    'To start a project'
                ],
                'correct_choice' => 2,
                'explanation' => 'To let the cat out of the bag means to reveal a secret or disclose information that was meant to be kept private.',
                'difficulty_level' => 'medium',
                'category' => 'Idioms'
            ],
            [
                'question_text' => 'Which sentence uses the correct tense sequence?',
                'choices' => [
                    'If I will go to the store, I will buy milk.',
                    'If I go to the store, I will buy milk.',
                    'If I went to the store, I will buy milk.',
                    'If I go to the store, I buy milk.'
                ],
                'correct_choice' => 2,
                'explanation' => 'In conditional sentences, the if-clause uses the present simple tense, and the main clause uses will + base form.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the correct use of "who" vs "whom"?',
                'choices' => [
                    'Who did you give the book to?',
                    'Whom did you give the book to?',
                    'To who did you give the book?',
                    'To whom did you give the book?'
                ],
                'correct_choice' => 4,
                'explanation' => 'Whom is used when it is the object of a verb or preposition. In this case, it is the object of the preposition "to".',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which sentence uses the correct article?',
                'choices' => [
                    'I saw a elephant at the zoo.',
                    'I saw an elephant at the zoo.',
                    'I saw the elephant at a zoo.',
                    'I saw elephant at the zoo.'
                ],
                'correct_choice' => 2,
                'explanation' => 'Use "an" before words that begin with a vowel sound. Elephant begins with a vowel sound, so "an" is correct.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the meaning of the word "ubiquitous"?',
                'choices' => [
                    'rare and unusual',
                    'present everywhere',
                    'very large',
                    'extremely small'
                ],
                'correct_choice' => 2,
                'explanation' => 'Ubiquitous means present, appearing, or found everywhere.',
                'difficulty_level' => 'medium',
                'category' => 'Vocabulary'
            ],
            [
                'question_text' => 'Which sentence uses the correct comparative form?',
                'choices' => [
                    'This book is more better than that one.',
                    'This book is better than that one.',
                    'This book is more good than that one.',
                    'This book is gooder than that one.'
                ],
                'correct_choice' => 2,
                'explanation' => 'Better is the comparative form of good. We don\'t use "more" with comparative adjectives that end in -er.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the correct use of "affect" vs "effect"?',
                'choices' => [
                    'The medicine had a positive affect on the patient.',
                    'The medicine had a positive effect on the patient.',
                    'The medicine will effect the patient\'s recovery.',
                    'The medicine will affect the patient\'s recovery.'
                ],
                'correct_choice' => 2,
                'explanation' => 'Effect is usually a noun meaning result, while affect is usually a verb meaning to influence.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which sentence uses the correct punctuation for dialogue?',
                'choices' => [
                    'He said, "I am going to the store."',
                    'He said "I am going to the store."',
                    'He said, I am going to the store.',
                    'He said "I am going to the store".'
                ],
                'correct_choice' => 1,
                'explanation' => 'In dialogue, use a comma before the quotation marks and place the period inside the quotation marks.',
                'difficulty_level' => 'medium',
                'category' => 'Punctuation'
            ],
            [
                'question_text' => 'What is the meaning of the idiom "to beat around the bush"?',
                'choices' => [
                    'To be direct and clear',
                    'To avoid the main topic',
                    'To work efficiently',
                    'To make a mistake'
                ],
                'correct_choice' => 2,
                'explanation' => 'To beat around the bush means to avoid talking about the main topic or to be indirect.',
                'difficulty_level' => 'medium',
                'category' => 'Idioms'
            ],
            [
                'question_text' => 'Which sentence uses the correct conditional form?',
                'choices' => [
                    'If I would have known, I would have come.',
                    'If I had known, I would have come.',
                    'If I knew, I would have come.',
                    'If I would know, I would have come.'
                ],
                'correct_choice' => 2,
                'explanation' => 'In the third conditional, use the past perfect in the if-clause and would have + past participle in the main clause.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the correct use of "lie" vs "lay"?',
                'choices' => [
                    'I will lay down for a nap.',
                    'I will lie down for a nap.',
                    'The book is laying on the table.',
                    'I need to lay the baby down.'
                ],
                'correct_choice' => 2,
                'explanation' => 'Lie means to recline and doesn\'t take an object. Lay means to put something down and requires an object.',
                'difficulty_level' => 'medium',
                'category' => 'Grammar'
            ],

            // Hard Questions (10)
            [
                'question_text' => 'Which sentence demonstrates the correct use of the subjunctive mood in a formal context?',
                'choices' => [
                    'I suggest that he goes to the doctor.',
                    'I suggest that he go to the doctor.',
                    'I suggest that he will go to the doctor.',
                    'I suggest that he went to the doctor.'
                ],
                'correct_choice' => 2,
                'explanation' => 'In formal English, the subjunctive mood is used after verbs like suggest, recommend, and insist. The base form of the verb is used without -s.',
                'difficulty_level' => 'hard',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the correct use of "whom" in the following sentence? "The person ___ I spoke to was very helpful."',
                'choices' => [
                    'who',
                    'whom',
                    'which',
                    'that'
                ],
                'correct_choice' => 2,
                'explanation' => 'Whom is used when it is the object of a verb or preposition. In this case, it is the object of the preposition "to".',
                'difficulty_level' => 'hard',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which sentence correctly uses the past perfect tense?',
                'choices' => [
                    'I had finished my homework when my friend called.',
                    'I finished my homework when my friend had called.',
                    'I have finished my homework when my friend called.',
                    'I finished my homework when my friend called.'
                ],
                'correct_choice' => 1,
                'explanation' => 'The past perfect (had + past participle) is used to show that one action was completed before another action in the past.',
                'difficulty_level' => 'hard',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the meaning of the word "ephemeral"?',
                'choices' => [
                    'lasting forever',
                    'lasting for a short time',
                    'extremely large',
                    'extremely small'
                ],
                'correct_choice' => 2,
                'explanation' => 'Ephemeral means lasting for a very short time. It comes from the Greek word "ephemeros" meaning "lasting only one day".',
                'difficulty_level' => 'hard',
                'category' => 'Vocabulary'
            ],
            [
                'question_text' => 'Which sentence demonstrates the correct use of the conditional perfect tense?',
                'choices' => [
                    'If I had studied harder, I would pass the exam.',
                    'If I had studied harder, I would have passed the exam.',
                    'If I studied harder, I would have passed the exam.',
                    'If I would have studied harder, I would have passed the exam.'
                ],
                'correct_choice' => 2,
                'explanation' => 'The conditional perfect (would have + past participle) is used to talk about hypothetical situations in the past that did not happen.',
                'difficulty_level' => 'hard',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the correct use of "comprise" vs "compose"?',
                'choices' => [
                    'The book is comprised of ten chapters.',
                    'The book is composed of ten chapters.',
                    'Ten chapters comprise the book.',
                    'Ten chapters compose the book.'
                ],
                'correct_choice' => 2,
                'explanation' => 'The whole comprises the parts, and the parts compose the whole. "Comprised of" is considered incorrect in formal writing.',
                'difficulty_level' => 'hard',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'Which sentence uses the correct parallel structure in a complex list?',
                'choices' => [
                    'The project requires researching, to write, and the presentation of findings.',
                    'The project requires researching, writing, and presenting findings.',
                    'The project requires to research, writing, and to present findings.',
                    'The project requires research, to write, and presenting findings.'
                ],
                'correct_choice' => 2,
                'explanation' => 'In a complex list, all items should use the same grammatical form. In this case, all gerunds (-ing forms) are used.',
                'difficulty_level' => 'hard',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the meaning of the word "pernicious"?',
                'choices' => [
                    'helpful and beneficial',
                    'harmful and destructive',
                    'temporary and fleeting',
                    'permanent and lasting'
                ],
                'correct_choice' => 2,
                'explanation' => 'Pernicious means having a harmful effect, especially in a gradual or subtle way.',
                'difficulty_level' => 'hard',
                'category' => 'Vocabulary'
            ],
            [
                'question_text' => 'Which sentence demonstrates the correct use of the subjunctive mood in a hypothetical situation?',
                'choices' => [
                    'If I was you, I would take the job.',
                    'If I were you, I would take the job.',
                    'If I am you, I will take the job.',
                    'If I be you, I would take the job.'
                ],
                'correct_choice' => 2,
                'explanation' => 'In hypothetical situations, the subjunctive mood uses "were" instead of "was", even with singular subjects.',
                'difficulty_level' => 'hard',
                'category' => 'Grammar'
            ],
            [
                'question_text' => 'What is the correct use of "furthermore" vs "moreover"?',
                'choices' => [
                    'Furthermore, the study shows that...',
                    'Moreover, the study shows that...',
                    'Both A and B are correct',
                    'Neither A nor B is correct'
                ],
                'correct_choice' => 3,
                'explanation' => 'Both "furthermore" and "moreover" are used to add information, but "moreover" suggests that the new information is more important than what came before.',
                'difficulty_level' => 'hard',
                'category' => 'Grammar'
            ]
        ];

        // Create the questions with mixed statuses to demonstrate corrector's capabilities
        foreach ($questions as $index => $questionData) {
            // Alternate between different statuses to show variety
            $status = match($index % 3) {
                0 => 'pending',
                1 => 'approved',
                2 => 'rejected'
            };

            $question = Question::create([
                ...$questionData,
                'created_by' => 3,
                'status' => $status,
                'approved_by' => $status === 'approved' ? 1 : null,
                'approved_at' => $status === 'approved' ? now() : null,
                'rejected_by' => $status === 'rejected' ? 1 : null,
                'rejected_at' => $status === 'rejected' ? now() : null,
                'rejection_reason' => $status === 'rejected' ? 'Sample rejection reason for testing' : null,
            ]);
        }
    }
}