<?php

namespace App\Services;

use App\Models\Category;

class CategoryServices
{
    /**
     * Get all categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Category::all();
    }

    /**
     * Find a category by ID.
     *
     * @param int $id
     * @return \App\Models\Category|null
     */
    public function findId($id)
    {
        return Category::find($id);
    }

    /**
     * Create a new category.
     *
     * @param array $data
     * @return \App\Models\Category
     */
    public function create(array $data)
    {
        return Category::create($data);
    }

    /**
     * Update an existing category.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Category
     */
    public function update($id, array $data)
    {
        $category = $this->findId($id);
        if ($category) {
            $category->update($data);
        }
        return $category;
    }

    /**
     * Delete a category.
     *
     * @param int $id
     * @return bool|null
     */
    public function delete($id)
    {
        $category = $this->findId($id);
        if ($category) {
            return $category->delete();
        }
        return null;
    }
}
