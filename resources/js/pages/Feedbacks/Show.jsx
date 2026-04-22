import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import PrimaryButton from '../../Components/PrimaryButton';

export default function FeedbacksShow({ feedback }) {
    const getStars = (rating) => {
        return Array.from({ length: 5 }, (_, i) => (
            <span
                key={i}
                className={`text-2xl ${
                    i < rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'
                }`}
            >
                ★
            </span>
        ));
    };

    const handleEdit = () => {
        router.visit(route('feedbacks.edit', feedback.id));
    };

    const handleDelete = () => {
        if (confirm('Tem certeza que deseja excluir esta avaliação? Esta ação não pode ser desfeita.')) {
            router.delete(route('feedbacks.destroy', feedback.id), {
                onSuccess: () => {
                    router.visit(route('books.show', feedback.book.id));
                }
            });
        }
    };

    return (
        <AppLayout>
            <Head title={`Avaliação de ${feedback.book.title}`} />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <div className="p-6">
                            {/* Header */}
                            <div className="flex justify-between items-start mb-8">
                                <div>
                                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                        Avaliação do Livro
                                    </h1>
                                    <p className="text-gray-600 dark:text-gray-400">
                                        ID da Avaliação: #{feedback.id}
                                    </p>
                                </div>
                                <div className="flex gap-2">
                                    <PrimaryButton onClick={handleEdit}>
                                        Editar Avaliação
                                    </PrimaryButton>
                                    <button
                                        onClick={handleDelete}
                                        className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-150"
                                    >
                                        Excluir
                                    </button>
                                </div>
                            </div>

                            {/* Book Information */}
                            <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    📚 Informações do Livro
                                </h2>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Título
                                        </span>
                                        <Link
                                            href={route('books.show', feedback.book.id)}
                                            className="text-lg font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300"
                                        >
                                            {feedback.book.title}
                                        </Link>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Autor
                                        </span>
                                        <span className="text-gray-900 dark:text-white">
                                            {feedback.book.author}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Gênero
                                        </span>
                                        <span className="text-gray-900 dark:text-white">
                                            {feedback.book.genre}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Ano
                                        </span>
                                        <span className="text-gray-900 dark:text-white">
                                            {feedback.book.year}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Review Details */}
                            <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    ⭐ Detalhes da Avaliação
                                </h2>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Avaliação
                                        </span>
                                        <div className="flex items-center mt-2">
                                            <div className="flex items-center mr-3">
                                                {getStars(feedback.rating)}
                                            </div>
                                            <span className="text-2xl font-bold text-gray-900 dark:text-white">
                                                {feedback.rating}/5
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Data da Avaliação
                                        </span>
                                        <span className="text-gray-900 dark:text-white">
                                            {new Date(feedback.created_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>

                                {feedback.comment && (
                                    <div className="mt-6">
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                            Comentário da Avaliação
                                        </span>
                                        <div className="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                            <p className="text-gray-900 dark:text-white leading-relaxed">
                                                {feedback.comment}
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Reviewer Information */}
                            <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    👤 Informações do Avaliador
                                </h2>
                                <div className="flex items-center space-x-4">
                                    <div className="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                        <span className="text-gray-600 dark:text-gray-300 font-semibold">
                                            {feedback.user.name.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Nome
                                        </span>
                                        <span className="text-gray-900 dark:text-white">
                                            {feedback.user.name}
                                        </span>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Email
                                        </span>
                                        <span className="text-gray-900 dark:text-white text-sm">
                                            {feedback.user.email}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                                <Link href={route('books.show', feedback.book.id)}>
                                    <PrimaryButton>
                                        Voltar ao Livro
                                    </PrimaryButton>
                                </Link>
                                <div className="flex gap-2">
                                    <PrimaryButton onClick={handleEdit}>
                                        Editar Avaliação
                                    </PrimaryButton>
                                    <button
                                        onClick={handleDelete}
                                        className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-150"
                                    >
                                        Excluir Avaliação
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
