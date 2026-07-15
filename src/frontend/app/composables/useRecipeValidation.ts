// composables/useRecipeSchema.ts (или прямо в компоненте)
import { z } from 'zod'

export const recipeSchema = z.object({
    title: z
        .string()
        .min(5, 'Введите название рецепта')
        .max(255, 'Название слишком длинное (макс. 255 символов)'),

    description: z
        .string()
        .min(10, 'Добавьте краткое описание')
        .max(150, 'Описание слишком длинное (макс. 150 символов)'),

    content: z
        .string()
        .min(1, 'Опишите шаги приготовления')
        .refine((val) => val.replace(/<[^>]*>/g, '').trim().length > 0, {
            message: 'Шаги приготовления не могут быть пустыми',
        }),

    categories: z
        .array(z.number())
        .min(1, 'Выберите хотя бы одну категорию'),

    total_time_minutes: z.coerce
        .number({ message: 'Введите время в минутах' })
        .int()
        .min(1, 'Время готовки должно быть больше 0')
        .max(1440, 'Слишком большое значение (макс. 1440 мин)'),

    portions: z.coerce
        .number({ message: 'Укажите количество порций' })
        .int()
        .min(1, 'Минимум 1 порция')
        .max(100, 'Слишком большое количество порций'),

    calories: z.coerce
        .number({ message: 'Укажите калорийность' })
        .int()
        .min(0, 'Калории не могут быть отрицательными')
        .max(10000, 'Слишком большое значение'),

    logo: z
        .instanceof(File, { message: 'Загрузите фото рецепта' })
        .refine((file) => file.size <= 2 * 1024 * 1024, 'Файл должен быть меньше 2MB')
        .refine(
            (file) => ['image/jpeg', 'image/png', 'image/jpg'].includes(file.type),
            'Разрешены только PNG или JPG'
        )
        .nullable()
        .refine((file) => file !== null, 'Загрузите фото рецепта'),
})

export type RecipeSchema = z.output<typeof recipeSchema>
