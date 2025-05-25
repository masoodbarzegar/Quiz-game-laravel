import React, { useState } from 'react';
import { useForm, router, usePage } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import TextArea from '@/Components/TextArea';
import PrimaryButton from '@/Components/PrimaryButton';

export default function QuestionForm({ question = null, difficultyLevels = {}, categories = [], mode = 'create' }) {
    const { data, setData, processing, errors } = useForm({
        question_text: question?.question_text ?? '',
        choices: question?.choices ?? ['', '', '', ''],
        correct_choice: question?.correct_choice ?? 1,
        explanation: question?.explanation ?? '',
        difficulty_level: question?.difficulty_level ?? 'medium',
        category: question?.category ?? '',
        status: question?.status ?? 'pending',
        rejection_reason: question?.rejection_reason ?? '',
    });
    const { auth } = usePage().props;
    const user = auth.user;

    const [draggedIndex, setDraggedIndex] = useState(null);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (mode === 'create') {
            router.post('/admin/questions', data);
        } else {
            router.put(`/admin/questions/${question.id}`, data);
        }
    };

    const handleChoiceChange = (index, value) => {
        const newChoices = [...data.choices];
        newChoices[index] = value;
        setData('choices', newChoices);
    };

    const handleDragStart = (index) => {
        setDraggedIndex(index);
    };

    const handleDragOver = (e, index) => {
        e.preventDefault();
        if (draggedIndex === null || draggedIndex === index) return;

        const newChoices = [...data.choices];
        const draggedChoice = newChoices[draggedIndex];
        newChoices.splice(draggedIndex, 1);
        newChoices.splice(index, 0, draggedChoice);

        // Update correct_choice if it was affected by the reordering
        let newCorrectChoice = data.correct_choice;
        if (draggedIndex + 1 === data.correct_choice) {
            newCorrectChoice = index + 1;
        } else if (index + 1 === data.correct_choice) {
            newCorrectChoice = draggedIndex + 1;
        }

        setData({
            ...data,
            choices: newChoices,
            correct_choice: newCorrectChoice,
        });
        setDraggedIndex(index);
    };

    const handleDragEnd = () => {
        setDraggedIndex(null);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div>
                <InputLabel htmlFor="question_text" value="Question Text" />
                <TextArea
                    id="question_text"
                    value={data.question_text}
                    onChange={(e) => setData('question_text', e.target.value)}
                    className="mt-1 block w-full"
                    required
                />
                <InputError message={errors.question_text} className="mt-2" />
            </div>

            <div className="space-y-4">
                <InputLabel value="Choices" />
                <div className="space-y-2">
                    {data.choices.map((choice, index) => (
                        <div
                            key={index}
                            draggable
                            onDragStart={() => handleDragStart(index)}
                            onDragOver={(e) => handleDragOver(e, index)}
                            onDragEnd={handleDragEnd}
                            className={`flex items-center space-x-4 p-2 rounded-md border ${
                                draggedIndex === index
                                    ? 'border-indigo-500 bg-indigo-50'
                                    : 'border-gray-300 hover:border-indigo-300'
                            } transition-colors duration-200`}
                        >
                            <div className="cursor-move text-gray-400 hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                                </svg>
                            </div>
                            <div className="flex-1">
                                <TextInput
                                    id={`choice_${index + 1}`}
                                    value={choice}
                                    onChange={(e) => handleChoiceChange(index, e.target.value)}
                                    className="block w-full"
                                    placeholder={`Choice ${index + 1}`}
                                    required
                                />
                                <InputError message={errors[`choices.${index}`]} className="mt-2" />
                            </div>
                            <div className="flex items-center">
                                <input
                                    type="radio"
                                    id={`correct_${index + 1}`}
                                    name="correct_choice"
                                    value={index + 1}
                                    checked={data.correct_choice === index + 1}
                                    onChange={(e) => setData('correct_choice', parseInt(e.target.value))}
                                    className="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label htmlFor={`correct_${index + 1}`} className="ml-2 text-sm text-gray-600">
                                    Correct
                                </label>
                            </div>
                        </div>
                    ))}
                </div>
                <p className="text-sm text-gray-500 mt-2">Drag and drop choices to reorder them</p>
                <InputError message={errors.choices} className="mt-2" />
                <InputError message={errors.correct_choice} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="explanation" value="Explanation (Optional)" />
                <TextArea
                    id="explanation"
                    value={data.explanation}
                    onChange={(e) => setData('explanation', e.target.value)}
                    className="mt-1 block w-full"
                />
                <InputError message={errors.explanation} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="difficulty_level" value="Difficulty Level" />
                <select
                    id="difficulty_level"
                    value={data.difficulty_level}
                    onChange={(e) => setData('difficulty_level', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                    {Object.entries(difficultyLevels).map(([value, label]) => (
                        <option key={value} value={value}>
                            {label}
                        </option>
                    ))}
                </select>
                <InputError message={errors.difficulty_level} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="category" value="Category (Optional)" />
                <input
                    type="text"
                    id="category"
                    list="categories"
                    value={data.category}
                    onChange={(e) => setData('category', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                <datalist id="categories">
                    {categories.map((category) => (
                        <option key={category} value={category} />
                    ))}
                </datalist>
                <InputError message={errors.category} className="mt-2" />
            </div>

            {mode === 'edit' && (user.role === 'manager' || user.role === 'corrector') && (
                <div>
                    <InputLabel htmlFor="status" value="Status" />
                    <select
                        id="status"
                        value={data.status}
                        onChange={(e) => setData('status', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <InputError message={errors.status} className="mt-2" />
                </div>
            )}

            {mode === 'edit' && (
                <>
                    {data.status === 'rejected' && (
                        <div>
                            <InputLabel htmlFor="rejection_reason" value="Rejection Reason" />
                            <TextArea
                                id="rejection_reason"
                                value={data.rejection_reason}
                                onChange={(e) => setData('rejection_reason', e.target.value)}
                                className="mt-1 block w-full"
                                required
                            />
                            <InputError message={errors.rejection_reason} className="mt-2" />
                        </div>
                    )}
                </>
            )}

            <div className="flex items-center justify-end">
                <PrimaryButton className="ml-4" disabled={processing}>
                    {mode === 'create' ? 'Create Question' : 'Update Question'}
                </PrimaryButton>
            </div>
        </form>
    );
} 