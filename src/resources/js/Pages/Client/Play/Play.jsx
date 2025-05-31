import React, { useState, useEffect, useCallback } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatTime } from '@/utils/time';

export default function Play({ game, session, questions, remainingLives, timeLimit }) {
    const [timeLeft, setTimeLeft] = useState(timeLimit);
    const [isGameActive, setIsGameActive] = useState(true);
    const [selectedAnswer, setSelectedAnswer] = useState(null);
    const [showFeedback, setShowFeedback] = useState(false);
    const [feedback, setFeedback] = useState({ correct: false, message: '' });
    const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
    const [startTime, setStartTime] = useState(Date.now());
    const [answers, setAnswers] = useState([]);
    const [score, setScore] = useState(0);
    const [lives, setLives] = useState(remainingLives);
    const [gameStartTime] = useState(Date.now());

    // Timer effect
    useEffect(() => {
        if (!isGameActive) return;

        const timer = setInterval(() => {
            setTimeLeft((prev) => {
                if (prev <= 1) {
                    clearInterval(timer);
                    handleGameEnd('timer');
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);

        return () => clearInterval(timer);
    }, [isGameActive]);

    // Reset startTime when question changes
    useEffect(() => {
        setStartTime(Date.now());
    }, [currentQuestionIndex]);
      
    const handleGameEnd = useCallback((reason, answersOverride = null) => {
        setIsGameActive(false);
        const totalTimeTaken = Math.floor((Date.now() - gameStartTime) / 1000);
        const currentQ = questions[currentQuestionIndex];
        const finalAnswers = [...(answersOverride || answers)];
        const validAnswers = finalAnswers.filter(answer => answer.selected_answer !== undefined);
        const totalQuestionsAnswered = validAnswers.length + 
            (currentQ && !validAnswers.some(a => a.question_id === currentQ.id) ? 1 : 0);

        const gameData = {
            answers: validAnswers.map(answer => ({
                question_id: answer.question_id,
                selected_answer: answer.selected_answer,
                is_correct: answer.is_correct,
                time_taken: answer.time_taken,
                points_earned: answer.points_earned,
                difficulty_level: answer.difficulty_level
            })),
            final_score: score,
            lives_remaining: lives,
            time_remaining: timeLeft,
            total_time_taken: totalTimeTaken,
            end_reason: reason,
            questions_answered: totalQuestionsAnswered,
            correct_answers: validAnswers.filter(a => a.is_correct).length,
            incorrect_answers: validAnswers.filter(a => !a.is_correct).length
        };

        router.post(
            `/play/${game.slug}/${session.id}/end`,
            gameData,
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    if (page.url) {
                        window.location.href = page.url;
                    }
                },
                onError: (errors) => {
                    setFeedback({
                        correct: null,
                        message: 'Error saving game data. Please try again.'
                    });
                }
            }
        );
    }, [game.slug, session.id, answers, score, lives, timeLeft, gameStartTime, currentQuestionIndex, questions]);

    const validateAnswer = (question, selectedIndex) => {
        // Compare selected index (0-based) with correct_choice (1-based)
        return (selectedIndex + 1) === question.correct_choice;
    };

    const getPointsForDifficulty = (difficulty) => {
        switch (difficulty) {
            case 'easy':
                return 3;  // 10 questions * 3 points = 30 points
            case 'medium':
                return 5;  // 6 questions * 5 points = 30 points
            case 'hard':
                return 8;  // 5 questions * 8 points = 40 points
            default:
                return 0;
        }
    };

    const handleAnswerSubmit = (choiceIndex) => {
        if (!isGameActive) return;
        
        setIsGameActive(false);
        const timeTaken = Math.floor((Date.now() - startTime) / 1000);
        const currentQ = questions[currentQuestionIndex];
        const isCorrect = validateAnswer(currentQ, choiceIndex);
        
        setSelectedAnswer(choiceIndex);
        const pointsEarned = isCorrect ? getPointsForDifficulty(currentQ.difficulty_level) : 0;

        if (isCorrect) {
            setScore(prev => prev + pointsEarned);
        }

        const answer = {
            question_id: currentQ.id,
            selected_answer: currentQ.choices[choiceIndex],
            is_correct: isCorrect,
            time_taken: timeTaken,
            points_earned: pointsEarned,
            difficulty_level: currentQ.difficulty_level
        };

        const updatedAnswers = [...answers, answer];
        setAnswers(updatedAnswers);

        setFeedback({
            correct: isCorrect,
            message: isCorrect ? `Correct! +${pointsEarned} points` : 'Incorrect!'
        });
        setShowFeedback(true);

        if (!isCorrect) {
            const newLives = lives - 1;
            setLives(newLives);
            
            if (newLives <= 0) {
                handleGameEnd('lives_exhausted', updatedAnswers);
                return;
            }
        }

        if (currentQuestionIndex + 1 >= questions.length) {
            setTimeout(() => {
                handleGameEnd('completed', updatedAnswers);
            }, 1500);
        } else {
            setTimeout(() => {
                setShowFeedback(false);
                setSelectedAnswer(null);
                setCurrentQuestionIndex(prev => prev + 1);
                setIsGameActive(true);
            }, 1500);
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Playing ${game.name}`} />

            <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                {/* Game Header */}
                <div className="bg-white shadow rounded-lg mb-6">
                    <div className="px-4 py-5 sm:p-6">
                        <div className="flex justify-between items-center">
                            <div className="flex items-center space-x-4">
                                <h1 className="text-2xl font-bold text-gray-900">{game.name}</h1>
                                <div className="flex items-center space-x-2">
                                    {[...Array(3)].map((_, index) => (
                                        <div
                                            key={index}
                                            className={`w-4 h-4 rounded-full ${
                                                index < lives ? 'bg-green-500' : 'bg-red-500'
                                            }`}
                                        />
                                    ))}
                                </div>
                                <div className="text-lg font-semibold text-gray-900">
                                    Score: {score}
                                </div>
                            </div>
                            <div className="flex items-center space-x-4">
                                <div className="text-2xl font-mono font-bold text-gray-900">
                                    {formatTime(timeLeft)}
                                </div>
                                <button
                                    onClick={() => handleGameEnd('user_exit')}
                                    className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Exit Game
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Question Card */}
                {!isGameActive && showFeedback && feedback.correct === null ? (
                    <div className="bg-white shadow rounded-lg">
                        <div className="px-4 py-5 sm:p-6">
                            <div className="text-center">
                                <h2 className="text-2xl font-bold text-gray-900 mb-4">{feedback.message}</h2>
                                <p className="text-lg text-gray-600 mb-4">Final Score: {score}</p>
                                <p className="text-lg text-gray-600">Redirecting to results...</p>
                            </div>
                        </div>
                    </div>
                ) : questions[currentQuestionIndex] && (
                    <div className="bg-white shadow rounded-lg">
                        <div className="px-4 py-5 sm:p-6">
                            <div className="space-y-6">
                                <div className="text-lg font-medium text-gray-900">
                                    Question {currentQuestionIndex + 1} of {questions.length}
                                </div>
                                <div className="text-xl font-medium text-gray-900">
                                    {questions[currentQuestionIndex].correct_choice}
                                    {questions[currentQuestionIndex].question_text}
                                </div>
                                <div className="grid grid-cols-1 gap-4 mt-4">
                                    {questions[currentQuestionIndex].choices.map((choice, index) => (
                                        <button
                                            key={index}
                                            onClick={() => handleAnswerSubmit(index)}
                                            disabled={showFeedback || !isGameActive}
                                            className={`p-4 text-left rounded-lg border ${
                                                selectedAnswer === index
                                                    ? feedback.correct
                                                        ? 'border-green-500 bg-green-50'
                                                        : 'border-red-500 bg-red-50'
                                                    : 'border-gray-300 hover:border-indigo-500'
                                            } ${
                                                showFeedback || !isGameActive
                                                    ? 'opacity-50 cursor-not-allowed'
                                                    : 'hover:bg-gray-50'
                                            }`}
                                        >
                                            {choice}
                                        </button>
                                    ))}
                                </div>
                                {showFeedback && feedback.correct !== null && (
                                    <div
                                        className={`mt-4 p-4 rounded-lg ${
                                            feedback.correct ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
                                        }`}
                                    >
                                        {feedback.message}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
} 