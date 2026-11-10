import { z } from 'zod'

export const createRecipeSchema = (t: (key: string) => string) => z.object({
    title: z
        .string()
        .min(5, t('recipe_validation_title_min'))
        .max(255, t('recipe_validation_title_max')),
    
    description: z
        .string()
        .min(10, t('recipe_validation_description_min'))
        .max(150, t('recipe_validation_description_max')),
    
    content: z
        .string()
        .min(1, t('recipe_validation_instructions_required'))
        .refine((val) => val.replace(/<[^>]*>/g, '').trim().length > 0, {
            message: t('recipe_validation_instructions_empty'),
        }),
    
    categories: z
        .array(z.number())
        .min(1, t('recipe_validation_categories')),

    ingredients: z
        .array(z.object({
            product_id: z.number({ message: t('recipe_validation_ingredient_product') })
                .int()
                .positive(t('recipe_validation_ingredient_product')),
            amount: z.number({ message: t('recipe_validation_ingredient_amount') })
                .positive(t('recipe_validation_ingredient_amount'))
                .max(1000000, t('recipe_validation_ingredient_amount')),
            unit_id: z.number({ message: t('recipe_validation_ingredient_unit') })
                .int()
                .positive(t('recipe_validation_ingredient_unit')),
        }))
        .min(1, t('recipe_validation_ingredients')),
    
    total_time_minutes: z.coerce
        .number({ message: t('recipe_validation_time_number') })
        .int()
        .min(1, t('recipe_validation_time_min'))
        .max(1440, t('recipe_validation_time_max')),
    
    portions: z.coerce
        .number({ message: t('recipe_validation_portions_number') })
        .int()
        .min(1, t('recipe_validation_portions_min'))
        .max(100, t('recipe_validation_portions_max')),
    
    calories: z.coerce
        .number({ message: t('recipe_validation_calories_number') })
        .int()
        .min(0, t('recipe_validation_calories_min'))
        .max(10000, t('recipe_validation_calories_max')),
    
    logo: z
    .instanceof(File)
    .refine((file) => file.size <= 2 * 1024 * 1024, t('recipe_validation_photo_size'))
    .refine(
        (file) => ['image/jpeg', 'image/png', 'image/jpg'].includes(file.type),
        t('recipe_validation_photo_type')
    )
    .nullish(),
})

export type RecipeSchema = z.output<ReturnType<typeof createRecipeSchema>>
