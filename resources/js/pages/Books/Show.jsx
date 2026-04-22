import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import PrimaryButton from '../../Components/PrimaryButton';

export default function BooksShow({ book, averageRating, canBorrow }) {
    const [showFeedbackForm, setShowFeedbackForm] = useState(false);
    const [rating, setRating] = useState(5);
    const [comment, setComment] = useState('');

    const handleBorrow = () => {
        router.post(route('books.borrow', book.id));
    };

    const handleFeedbackSubmit = (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('rating', rating);
        formData.append('comment', comment);

        router.post(route('books.feedback.store', book.id), formData, {
            onSuccess: () => {
                setShowFeedbackForm(false);
                setRating(5);
                setComment('');
                router.reload();
            },
        });
    };

    return (
        <AppLayout>
            <Head title={`${book.title} - Detalhes do Livro`} />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    {/* Book Header */}
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <div className="md:flex">
                            <div className="md:flex-shrink-0">
                                <div className="h-48 w-full object-cover md:h-full md:w-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span className="text-gray-500 dark:text-gray-400 text-2xl">📚</span>
                                </div>
                            </div>
                            <div className="p-8 flex-1">
                                <div className="flex justify-between items-start mb-4">
                                    <div>
                                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                                            {book.title}
                                        </h1>
                                        <p className="text-lg text-gray-600 dark:text-gray-400 mt-1">
                                            por {book.author}
                                        </p>
                                    </div>
                                    <div className="flex gap-2">
                                        <Link href={route('books.edit', book.id)}>
                                            <PrimaryButton>
                                                Editar Livro
                                            </PrimaryButton>
                                        </Link>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Gênero
                                        </span>
                                        <span className="block text-gray-900 dark:text-white">
                                            {book.genre}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Ano
                                        </span>
                                        <span className="block text-gray-900 dark:text-white">
                                            {book.year}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            ISBN
                                        </span>
                                        <span className="block text-gray-900 dark:text-white text-sm">
                                            {book.isbn}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Estoque
                                        </span>
                                        <span className={`block font-semibold ${
                                            book.loans_count < book.stock
                                                ? 'text-green-600 dark:text-green-400'
                                                : 'text-red-600 dark:text-red-400'
                                        }`}>
                                            {book.stock - book.loans_count} disponíveis
                                        </span>
                                    </div>
                                </div>

                                {book.description && (
                                    <div className="mb-6">
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                            Descrição
                                        </h3>
                                        <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                                            {book.description}
                                        </p>
                                    </div>
                                )}

                                {/* Action Buttons */}
                                <div className="flex gap-4 mb-6">
                                    {canBorrow ? (
                                        <PrimaryButton onClick={handleBorrow}>
                                            Pegar Este Livro Emprestado
                                        </PrimaryButton>
                                    ) : (
                                        <div className="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md cursor-not-allowed">
                                            {book.loans_count >= book.stock ? 'Todas as Cópias Emprestadas' : 'Não é Possível Emprestar'}
                                        </div>
                                    )}
                                </div>

                                {/* Rating Summary */}
                                {averageRating > 0 && (
                                    <div className="border-t border-gray-200 dark:border-gray-700 pt-6">
                                        <div className="flex items-center gap-4">
                                            <div className="flex items-center">
                                                {[1, 2, 3, 4, 5].map((star) => (
                                                    <span
                                                        key={star}
                                                        className={`text-2xl ${
                                                            star <= Math.round(averageRating)
                                                                ? 'text-yellow-400'
                                                                : 'text-gray-300 dark:text-gray-600'
                                                        }`}
                                                    >
                                                        ★
                                                    </span>
                                                ))}
                                            </div>
                                            <span className="text-lg font-semibold text-gray-900 dark:text-white">
                                                {averageRating}
                                            </span>
                                            <span className="text-sm text-gray-600 dark:text-gray-400">
                                                ({book.feedbacks_count} avaliações)
                                            </span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Active Loans */}
                    {book.loans && book.loans.length > 0 && (
                        <div className="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                Empréstimos Ativos
                            </h2>
                            <div className="space-y-3">
                                {book.loans.map((loan) => (
                                    <div
                                        key={loan.id}
                                        className="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded"
                                    >
                                        <div>
                                            <span className="font-medium text-gray-900 dark:text-white">
                                                {loan.user.name}
                                            </span>
                                            <span className="text-sm text-gray-600 dark:text-gray-400 ml-2">
                                                Devolução: {new Date(loan.due_date).toLocaleDateString()}
                                            </span>
                                        </div>
                                        <span className={`px-2 py-1 text-xs rounded ${
                                            new Date(loan.due_date) < new Date()
                                                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                        }`}>
                                            {new Date(loan.due_date) < new Date() ? 'Atrasado' : 'Ativo'}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Feedbacks */}
                    {book.feedbacks && book.feedbacks.length > 0 && (
                        <div className="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                Avaliações
                            </h2>
                            <div className="space-y-4">
                                {book.feedbacks.map((feedback) => (
                                    <div
                                        key={feedback.id}
                                        className="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0"
                                    >
                                        <div className="flex justify-between items-start mb-2">
                                            <div>
                                                <span className="font-medium text-gray-900 dark:text-white">
                                                    {feedback.user.name}
                                                </span>
                                                <div className="flex items-center mt-1">
                                                    {[1, 2, 3, 4, 5].map((star) => (
                                                        <span
                                                            key={star}
                                                            className={`text-sm ${
                                                                star <= feedback.rating
                                                                    ? 'text-yellow-400'
                                                                    : 'text-gray-300 dark:text-gray-600'
                                                            }`}
                                                        >
                                                            ★
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                            <span className="text-sm text-gray-600 dark:text-gray-400">
                                                {new Date(feedback.created_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                        {feedback.comment && (
                                            <p className="text-gray-600 dark:text-gray-400">
                                                {feedback.comment}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Add Feedback Form */}
                    <div className="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div className="flex justify-between items-center mb-4">
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white">
                                Escrever uma Avaliação
                            </h2>
                            <button
                                onClick={() => setShowFeedbackForm(!showFeedbackForm)}
                                className="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300"
                            >
                                {showFeedbackForm ? 'Cancelar' : 'Adicionar Avaliação'}
                            </button>
                        </div>

                        {showFeedbackForm && (
                            <form onSubmit={handleFeedbackSubmit}>
                                <div className="mb-4">
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Classificação
                                    </label>
                                    <select
                                        value={rating}
                                        onChange={(e) => setRating(e.target.value)}
                                        className="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    >
                                        <option value={5}>⭐⭐⭐⭐⭐⭐ Excelente</option>
                                        <option value={4}>⭐⭐⭐⭐ Bom</option>
                                        <option value={3}>⭐⭐⭐ Médio</option>
                                        <option value={2}>⭐⭐ Ruim</option>
                                        <option value={1}>⭐ Muito Ruim</option>
                                    </select>
                                </div>

                                <div className="mb-4">
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Comentário (opcional)
                                    </label>
                                    <textarea
                                        value={comment}
                                        onChange={(e) => setComment(e.target.value)}
                                        rows={4}
                                        className="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                        placeholder="Compartilhe seus pensamentos sobre este livro..."
                                    />
                                </div>

                                <PrimaryButton type="submit">
                                    Enviar Avaliação
                                </PrimaryButton>
                            </form>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
