import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthCard from '@/Components/AuthCard';
import Input from '@/Components/Input';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import Checkbox from '@/Components/Checkbox';

interface LoginFormData {
    email: string;
    password: string;
    remember: boolean;
}

interface LoginProps {
    canResetPassword: boolean;
    status: string | null;
}

export default function Login({ canResetPassword, status }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginFormData>({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthCard>
            <Head title="Login" />

            <div className="text-center mb-8">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                    Sistema de Livraria
                </h1>
            </div>

            {status && (
                <div className="mb-4 text-sm text-green-600 dark:text-green-400 text-center">
                    {status}
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-5">
                <div>
                    <InputLabel htmlFor="email" value="E-mail" className="mb-1.5" />
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        placeholder="Digite seu email"
                        className="block w-full px-4 py-3"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.email} />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Senha" className="mb-1.5" />
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        placeholder="Digite sua senha"
                        className="block w-full px-4 py-3"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.password} />
                </div>

                <div className="flex items-center justify-between">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <span className="ms-2 text-sm text-gray-600 dark:text-gray-400">
                            Lembrar-me
                        </span>
                    </label>

                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                        >
                            Esqueceu sua senha?
                        </Link>
                    )}
                </div>

                <PrimaryButton
                    className="w-full py-3 text-base font-medium"
                    disabled={processing}
                >
                    Entrar
                </PrimaryButton>
            </form>
        </AuthCard>
    );
}
