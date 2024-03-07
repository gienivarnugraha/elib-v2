class Gate {
    /**
     * Initialize a new gate instance.
     *
     * @param  {Object}  user
     * @param  {String}  authorizationProperty
     * @return {Void}
     */
    constructor(user, authorizationProperty = 'authorizations') {
      this.user = user
      this.authorizationProperty = authorizationProperty
    }
  
    /**
     * Check if the user is super admin
     *
     * @return {Boolean}
     */
    isSuperAdmin() {
      return this.user.roles.includes('super-admin')
    }
  
    /**
     * Check if the user is regular user
     *
     * @return {Boolean}
     */
    isRegularUser() {
      return !this.isSuperAdmin()
    }
  
    /**
     * Check whether a user can perform specific action/ability
     *
     * @param  {String} ability
     *
     * @return {Boolean}
     */
    userCan(ability) {
      if (this.isSuperAdmin()) {
        return true
      }
  
      if (this.user.gate.hasOwnProperty(ability)) {
        return this.user.gate[ability]
      }
  
      return this.user.permissions.indexOf(ability) > -1
    }
  
    /**
     * Check whether a user cant perform specific action/ability
     *
     * @param  {String} ability
     *
     * @return {Boolean}
     */
    userCant(ability) {
      return !this.userCan(ability)
    }
  
    /**
     * Determine wheter the user can perform the action on the record.
     *
     * @param  {String}      ability
     * @param  {Object}      record
     *
     * @return {Boolean}
     */
    allows(ability, record) {
      if (this.isSuperAdmin()) {
        return true
      }
  
      if (this.user && record.hasOwnProperty(this.authorizationProperty)) {
        return record[this.authorizationProperty][ability]
      }
  
      return false
    }
  
    /**
     * Determine wheter the user can't perform the action on the record.
     *
     * @param  {String}      ability
     * @param  {Object}      record
     *
     * @return {Boolean}
     */
    denies(ability, record) {
      return !this.allows(ability, record)
    }
  }
  
  export default Gate